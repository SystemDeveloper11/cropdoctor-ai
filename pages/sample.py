from _future_ import annotations

import base64
import io
import json
import os
import re
import time
import logging
from datetime import datetime
from typing import Dict, Optional
from functools import wraps

import requests
import google.generativeai as genai
from flask import (
    Flask,
    flash,
    redirect,
    render_template,
    request,
    send_file,
    url_for,
    jsonify,
    abort,
    send_from_directory,
)
from flask_login import (
    LoginManager,
    UserMixin,
    current_user,
    login_required,
    login_user,
    logout_user,
)
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
from werkzeug.exceptions import RequestEntityTooLarge
from bson import ObjectId

from config import config
from models import get_collections, create_admin_user, get_db

from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Image as ReportLabImage
from reportlab.lib.units import inch

from gridfs import GridFS

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(_name_)

# Configure Gemini
genai.configure(api_key=config.GEMINI_API_KEY)


app = Flask(_name_)
app.config["SECRET_KEY"] = config.SECRET_KEY
app.config["UPLOAD_FOLDER"] = config.UPLOAD_FOLDER
app.config["MAX_CONTENT_LENGTH"] = config.MAX_CONTENT_LENGTH

# Security headers
@app.after_request
def after_request(response):
    response.headers['X-Content-Type-Options'] = 'nosniff'
    response.headers['X-Frame-Options'] = 'DENY'
    response.headers['X-XSS-Protection'] = '1; mode=block'
    response.headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains'
    return response

login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = "login"
login_manager.login_message = "Please log in to access this page."
login_manager.login_message_category = "info"


@app.context_processor
def inject_user_info():
    if current_user.is_authenticated:
        users, _, _ = get_collections()
        user = users.find_one({"_id": ObjectId(current_user.id)})
        return dict(is_admin=user.get('is_admin', False) if user else False)
    return dict(is_admin=False)


class UserLogin(UserMixin):
    def _init_(self, id: str):
        self.id = str(id)


@login_manager.user_loader
def load_user(user_id):
    users, _, _ = get_collections()
    user = users.find_one({"_id": ObjectId(user_id)}) if ObjectId.is_valid(user_id) else None
    if user:
        return UserLogin(str(user["_id"]))
    return None


try:
    os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)
except PermissionError as e:
    logger.warning(f"Could not create UPLOAD_FOLDER {app.config['UPLOAD_FOLDER']}: {e}. Falling back to /app/uploads")
    # Fallback to application-owned uploads dir (created in Dockerfile)
    fallback = "/app/uploads"
    try:
        os.makedirs(fallback, exist_ok=True)
        app.config["UPLOAD_FOLDER"] = fallback
    except Exception:
        # If fallback also fails, re-raise the original permission error
        raise e


ALLOWED_EXTENSIONS = config.ALLOWED_EXTENSIONS


def allowed_file(filename: str) -> bool:
    if "." not in filename:
        return False
    ext = filename.rsplit(".", 1)[1].lower()
    return ext in ALLOWED_EXTENSIONS


# Gemini prompt for plant analysis
GEMINI_PROMPT = (
    "Analyze this plant image for a Kenyan farmer. Identify the plant type, what part is shown, and describe what you see. "
    "Look for any signs of disease, pests, or damage. Respond exactly in this format:\n\n"
    "Plant Type: [name the specific plant/crop you see]\n"
    "Plant Part: [leaf/stem/fruit/flower/root/whole plant]\n"
    "Health Status: [Healthy or Unhealthy]\n"
    "Visual Observations: [describe colors, spots, damage, or other details you see]\n"
    "Preliminary Diagnosis: [if unhealthy, suggest what might be wrong, or 'None' if healthy]\n"
    "Issue: [disease/pest or 'Healthy']\n"
    "Confidence: [percentage]\n"
    "Symptoms: [brief description]\n"
    "Cause: [root cause]\n"
    "Remedies: [3-4 practical solutions]\n"
    "Prevention: [preventive measures]\n"
    "Urgency: [Low/Medium/High]\n"
)


def call_gemini_with_image(prompt: str, image_path: str) -> str:
    """Call Gemini with an image and prompt."""
    model = genai.GenerativeModel('gemini-2.0-flash-001')
    with open(image_path, "rb") as f:
        image_bytes = f.read()
    
    image_parts = [
        {
            "mime_type": "image/jpeg",
            "data": image_bytes
        }
    ]
    
    prompt_parts = [
        prompt,
        image_parts[0]
    ]
    
    response = model.generate_content(prompt_parts)
    return response.text


def parse_gemini_analysis(text: str) -> Dict[str, str]:
    """Parse Gemini's analysis"""
    lines = [l.strip() for l in text.splitlines() if l.strip()]
    
    result = {
        "plant_type": "Unknown",
        "plant_part": "Unknown", 
        "health_status": "Unknown",
        "visual_observations": "No observations",
        "preliminary_diagnosis": "None",
        "issue": "Unknown",
        "confidence": "-",
        "symptoms": "-",
        "cause": "-",
        "remedies": "-",
        "prevention": "-",
        "urgency": ""
    }
    
    logger.info(f"Raw Gemini response: {text[:200]}...")
    
    current_key = None
    for line in lines:
        if ":" in line:
            key, value = line.split(":", 1)
            key = key.strip().lower().replace(" ", "_")
            if key in result:
                current_key = key
                result[current_key] = value.strip()
            elif current_key:
                result[current_key] += "\n" + line.strip()
        elif current_key:
            result[current_key] += "\n" + line.strip()

    for key, value in result.items():
        if value.startswith("[") and value.endswith("]"):
            result[key] = value[1:-1]

    return result


def perform_analysis(image_path: str) -> Dict[str, str]:
    """Perform analysis using Gemini"""
    try:
        logger.info("🔍 Starting Gemini vision analysis...")
        print("🔍 Analyzing image with Gemini vision model...")
        
        gemini_response = call_gemini_with_image(GEMINI_PROMPT, image_path)
        analysis_result = parse_gemini_analysis(gemini_response)
        
        logger.info(f"✅ Gemini analysis complete. Issue: {analysis_result.get('issue', 'Unknown')}")
        print(f"✅ Complete analysis finished. Detected issue: {analysis_result.get('issue', 'Unknown')}")
        return analysis_result
        
    except Exception as e:
        logger.error(f"❌ Gemini analysis failed: {e}")
        print(f"❌ Vision analysis failed: {e}")
        return {
            "plant_type": "Unknown",
            "plant_part": "Unknown", 
            "issue": "Analysis failed",
            "confidence": "0%",
            "symptoms": "Could not analyze image",
            "cause": f"Technical error: {str(e)}",
            "remedies": "Please try again or consult with a local agricultural extension officer",
            "prevention": "Regular monitoring and proper plant care",
            "urgency": "Medium",
            "visual_observations": "Analysis could not be completed"
        }


@app.route("/")
def landing():
    if current_user.is_authenticated:
        return redirect(url_for("dashboard"))
    return render_template("landing.html")


@app.route("/dashboard", methods=["GET", "POST"])
@login_required
def dashboard():
    # Check user subscription
    users, diagnoses, subscriptions = get_collections()
    db = get_db()
    fs = GridFS(db)
    subscription = subscriptions.find_one({"user_id": ObjectId(current_user.id)})
    
    if request.method == "POST":
        # Check if user has reached their limit
        if subscription and subscription.get('diagnoses_used', 0) >= subscription.get('diagnoses_limit', 10):
            flash("You have reached your diagnosis limit. Please upgrade your plan.", "error")
            return redirect(request.url)
        if "image" not in request.files:
            flash("No file part in the request.", "error")
            return redirect(request.url)
        file = request.files["image"]
        # Plant part is now optional - will be detected automatically
        plant_part = request.form.get("plant_part") or None

        if file.filename == "":
            flash("No file selected.", "error")
            return redirect(request.url)
        if not allowed_file(file.filename):
            flash("Invalid file type. Please upload a JPG or PNG image.", "error")
            return redirect(request.url)

        filename = secure_filename(file.filename)
        image_id = fs.put(file, filename=filename)

        # Create a temporary file to pass to the analysis function
        with fs.get(image_id) as f:
            tmp_path = os.path.join(app.config["UPLOAD_FOLDER"], str(image_id))
            with open(tmp_path, 'wb') as tmp_file:
                tmp_file.write(f.read())

        try:
            # Use the new analysis pipeline
            analysis_result = perform_analysis(tmp_path)
            
            # Use user-provided plant part if available, otherwise use detected
            final_plant_part = plant_part if plant_part else analysis_result.get("plant_part", "Unknown")
            
        except requests.Timeout:
            flash("Analysis request timed out. Please try again.", "error")
            return redirect(request.url)
        except requests.HTTPError as he:
            flash(f"Analysis error: {he}", "error")
            return redirect(request.url)
        except Exception as e:
            flash(f"Diagnosis failed: {e}", "error")
            return redirect(request.url)
        finally:
            # Clean up the temporary file
            if os.path.exists(tmp_path):
                os.remove(tmp_path)

        doc = {
            "user_id": ObjectId(current_user.id),
            "image_id": image_id,
            "plant_type": analysis_result.get("plant_type", "Unknown"),  # Auto-detected plant type
            "crop_type": analysis_result.get("plant_type", "Unknown"),   # For template compatibility
            "plant_part": final_plant_part,  # Optional user input or auto-detected
            "issue": analysis_result.get("issue", "Unknown"),
            "confidence": analysis_result.get("confidence", "-"),
            "symptoms": analysis_result.get("symptoms", "-"),
            "cause": analysis_result.get("cause", "-"),
            "remedies": analysis_result.get("remedies", "-"),
            "prevention": analysis_result.get("prevention", "-"),
            "urgency": analysis_result.get("urgency", "Medium"),
            "visual_observations": analysis_result.get("visual_observations", "No observations"),
            "created_at": datetime.utcnow(),
        }
        result = diagnoses.insert_one(doc)
        
        # Update subscription usage
        if subscription:
            subscriptions.update_one(
                {"user_id": ObjectId(current_user.id)},
                {"$inc": {"diagnoses_used": 1}}
            )
        return redirect(url_for("result", diagnosis_id=str(result.inserted_id)))

    return render_template("dashboard.html", subscription=subscription)


@app.route("/result/<diagnosis_id>")
@login_required
def result(diagnosis_id: str):
    _, diagnoses, _ = get_collections()
    if not ObjectId.is_valid(diagnosis_id):
        flash("Invalid diagnosis id.", "error")
        return redirect(url_for("dashboard"))
    diag = diagnoses.find_one({"_id": ObjectId(diagnosis_id), "user_id": ObjectId(current_user.id)})
    if not diag:
        flash("Diagnosis not found.", "error")
        return redirect(url_for("dashboard"))
    remedies_list = [r.strip("- ") for r in re.split(r"[\n\r]+", diag.get("remedies") or "") if r.strip()]
    return render_template("result.html", diag=diag, remedies_list=remedies_list)


@app.route("/history")
@login_required
def history():
    _, diagnoses, _ = get_collections()
    entries = list(diagnoses.find({"user_id": ObjectId(current_user.id)}).sort("created_at", -1).limit(100))
    return render_template("history.html", entries=entries)


@app.route("/diagnosis/<diagnosis_id>/pdf")
@login_required
def diagnosis_pdf(diagnosis_id: str):
    _, diagnoses, _ = get_collections()
    if not ObjectId.is_valid(diagnosis_id):
        flash("Invalid diagnosis id.", "error")
        return redirect(url_for("dashboard"))
    diag = diagnoses.find_one({"_id": ObjectId(diagnosis_id), "user_id": ObjectId(current_user.id)})
    if not diag:
        flash("Diagnosis not found.", "error")
        return redirect(url_for("dashboard"))

    buffer = io.BytesIO()
    c = canvas.Canvas(buffer, pagesize=A4)
    width, height = A4
    y = height - 72

    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, y, "Crop Doctor Diagnosis")
    y -= 28

    c.setFont("Helvetica", 12)
    fields = [
        ("Date", diag.get("created_at").strftime("%Y-%m-%d %H:%M") if diag.get("created_at") else "-"),
        ("Plant Type", diag.get("plant_type") or diag.get("crop_type") or "-"),  # Backward compatibility
        ("Plant Part", diag.get("plant_part") or "-"),
        ("Issue", diag.get("issue")),
        ("Confidence", diag.get("confidence") or "-"),
        ("Symptoms", diag.get("symptoms") or "-"),
        ("Cause", diag.get("cause") or "-"),
        ("Remedies", diag.get("remedies") or "-"),
        ("Prevention", diag.get("prevention") or "-"),
        ("Urgency", diag.get("urgency") or "-"),
    ]
    for label, value in fields:
        c.setFont("Helvetica-Bold", 12)
        c.drawString(72, y, f"{label}:")
        c.setFont("Helvetica", 12)
        text = c.beginText(150, y)
        for line in (value or "-").splitlines() or ["-"]:
            text.textLine(line)
        c.drawText(text)
        y -= max(22, 16 * (len((value or "-").splitlines()) + 1))
        if y < 72:
            c.showPage()
            y = height - 72

    c.showPage()
    c.save()
    buffer.seek(0)
    return send_file(
        buffer,
        as_attachment=True,
        download_name=f"diagnosis_{diagnosis_id}.pdf",
        mimetype="application/pdf",
    )


@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        email = request.form.get("email", "").strip().lower()
        password = request.form.get("password", "")
        users, _, _ = get_collections()
        user = users.find_one({"email": email})
        if user and check_password_hash(user.get("password_hash", ""), password):
            login_user(UserLogin(str(user["_id"])) )
            next_page = request.args.get('next')
            return redirect(next_page) if next_page else redirect(url_for("dashboard"))
        flash("Invalid credentials", "error")
    return render_template("login.html")


@app.route("/register", methods=["GET", "POST"])
def register():
    if request.method == "POST":
        email = request.form.get("email", "").strip().lower()
        password = request.form.get("password", "")
        if not email or not password:
            flash("Email and password are required.", "error")
            return redirect(request.url)
        users, _, subscriptions = get_collections()
        existing = users.find_one({"email": email})
        if existing:
            flash("Email already registered.", "error")
            return redirect(request.url)
        user_doc = {
            "email": email, 
            "password_hash": generate_password_hash(password), 
            "is_admin": False,
            "created_at": datetime.utcnow()
        }
        result = users.insert_one(user_doc)
        
        # Create default subscription
        subscription_doc = {
            "user_id": result.inserted_id,
            "plan": "free",
            "status": "active",
            "diagnoses_used": 0,
            "diagnoses_limit": 10,
            "created_at": datetime.utcnow(),
            "expires_at": None
        }
        subscriptions.insert_one(subscription_doc)
        flash("Account created. Please log in.", "success")
        return redirect(url_for("login"))
    return render_template("register.html")


@app.route("/logout")
@login_required
def logout():
    logout_user()
    return redirect(url_for("login"))


def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated:
            return redirect(url_for('login'))
        users, _, _ = get_collections()
        user = users.find_one({"_id": ObjectId(current_user.id)})
        if not user or not user.get('is_admin', False):
            abort(403)
        return f(*args, **kwargs)
    return decorated_function


@app.route("/admin")
@admin_required
def admin_dashboard():
    users, diagnoses, subscriptions = get_collections()
    
    # Get stats
    total_users = users.count_documents({})
    total_diagnoses = diagnoses.count_documents({})
    active_subscriptions = subscriptions.count_documents({"status": "active"})
    
    # Get recent users
    recent_users = list(users.find({}).sort("created_at", -1).limit(10))
    
    # Get subscription stats
    subscription_stats = list(subscriptions.aggregate([
        {"$group": {"_id": "$plan", "count": {"$sum": 1}}}
    ]))
    
    return render_template("admin/dashboard.html", 
                         total_users=total_users,
                         total_diagnoses=total_diagnoses,
                         active_subscriptions=active_subscriptions,
                         recent_users=recent_users,
                         subscription_stats=subscription_stats)


@app.route("/admin/users")
@admin_required
def admin_users():
    users, _, subscriptions = get_collections()
    
    # Get users with their subscription info - simpler approach
    all_users = list(users.find({}).sort("created_at", -1))
    users_with_subs = []
    
    for user in all_users:
        subscription = subscriptions.find_one({"user_id": user["_id"]})
        user["subscription"] = subscription
        users_with_subs.append(user)
    
    return render_template("admin/users.html", users=users_with_subs)


@app.route("/admin/users/<user_id>/subscription", methods=["POST"])
@admin_required
def update_user_subscription(user_id):
    if not ObjectId.is_valid(user_id):
        flash("Invalid user ID", "error")
        return redirect(url_for("admin_users"))
    
    users, _, subscriptions = get_collections()
    plan = request.form.get("plan")
    status = request.form.get("status")
    diagnoses_limit = int(request.form.get("diagnoses_limit", 10))
    
    # Update subscription
    subscriptions.update_one(
        {"user_id": ObjectId(user_id)},
        {"$set": {
            "plan": plan,
            "status": status,
            "diagnoses_limit": diagnoses_limit,
            "updated_at": datetime.utcnow()
        }},
        upsert=True
    )
    
    flash("Subscription updated successfully", "success")
    return redirect(url_for("admin_users"))


@app.route("/admin/users/<user_id>/delete", methods=["POST"])
@admin_required
def delete_user(user_id):
    if not ObjectId.is_valid(user_id):
        flash("Invalid user ID", "error")
        return redirect(url_for("admin_users"))
    
    users, diagnoses, subscriptions = get_collections()
    
    # Delete user and related data
    users.delete_one({"_id": ObjectId(user_id)})
    diagnoses.delete_many({"user_id": ObjectId(user_id)})
    subscriptions.delete_many({"user_id": ObjectId(user_id)})
    
    flash("User deleted successfully", "success")
    return redirect(url_for("admin_users"))


@app.route('/sitemap.xml')
def sitemap():
    """Generate sitemap for SEO"""
    from flask import make_response
    
    sitemap_xml = '''<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>{}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
</urlset>'''.format(
        url_for('landing', _external=True),
        url_for('history', _external=True),
        url_for('login', _external=True),
        url_for('register', _external=True)
    )
    
    response = make_response(sitemap_xml)
    response.headers["Content-Type"] = "application/xml"
    return response

@app.route('/robots.txt')
def robots():
    """Serve robots.txt"""
    return send_file('static/robots.txt', mimetype='text/plain')

@app.route('/image/<image_id>')
def image(image_id):
    """Serve an image from GridFS."""
    if not ObjectId.is_valid(image_id):
        abort(404)
    db = get_db()
    fs = GridFS(db)
    try:
        grid_out = fs.get(ObjectId(image_id))
        return send_file(grid_out, mimetype='image/jpeg')
    except Exception as e:
        logger.error(f"Error serving image {image_id}: {e}")
        abort(404)

@app.route('/favicon.ico')
def favicon():
    """Serve favicon"""
    return '', 204  # No content response for favicon

@app.route("/health")
def health_check():
    """Health check endpoint for monitoring"""
    try:
        # Test database connection
        users, diagnoses, _ = get_collections()
        users.find_one()
        return jsonify({
            'status': 'healthy',
            'timestamp': datetime.utcnow().isoformat(),
            'version': '2.0.0'
        })
    except Exception as e:
        logger.error(f"Health check failed: {e}")
        return jsonify({
            'status': 'unhealthy',
            'error': str(e),
            'timestamp': datetime.utcnow().isoformat()
        }), 500

@app.errorhandler(413)
def request_entity_too_large(e):
    flash("File is too large. Maximum size is 5MB.", "error")
    return redirect(url_for("dashboard"))

@app.errorhandler(404)
def not_found(e):
    return render_template('404.html'), 404

@app.errorhandler(500)
def internal_error(e):
    logger.error(f"Internal server error: {e}")
    return render_template('500.html'), 500


if _name_ == "_main_":
    # Create admin user on startup
    create_admin_user()
    app.run(host="0.0.0.0", port=5000, debug=True)

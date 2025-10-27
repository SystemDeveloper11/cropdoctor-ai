<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CropDoctor AI | AI-Powered Plant Disease Diagnosis</title>
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>

.footer-title {
    font-weight: 600;
    margin-bottom: 15px;
    color: #ffffff;
    font-size: 1.2rem;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #dcdcdc;
    text-decoration: none;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    transition: color 0.3s ease, transform 0.3s ease;
}

.footer-links a i {
    color: #00d084;
    margin-right: 10px;
    font-size: 1.1rem;
    width: 22px;
    text-align: center;
    transition: transform 0.3s ease;
}

.footer-links a:hover {
    color: #00d084;
    transform: translateX(4px);
}

.footer-links a:hover i {
    transform: scale(1.2);
}


        :root {
            --primary: #1a5d1a;
            --primary-light: #2e8b2e;
            --primary-dark: #0d3d0d;
            --secondary: #e6b325;
            --accent: #4a7c59;
            --light: #f8f9fa;
            --dark: #1e2a1e;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            --gradient-light: linear-gradient(135deg, var(--primary-light) 0%, #4aab4a 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            background-color: #fefefe;
            overflow-x: hidden;
            line-height: 1.7;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            line-height: 1.3;
        }
        
        /* Header Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 20px 0;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
        }
        
        .navbar.scrolled {
            padding: 12px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
            color: var(--secondary);
            font-size: 2rem;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 12px;
            color: var(--dark);
            transition: all 0.3s;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-primary {
            background: var(--gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
            box-shadow: 0 4px 15px rgba(26, 93, 26, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 93, 26, 0.3);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.4s;
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.2);
        }
        
        /* Hero Section */
        .hero {
            background: var(--gradient);
            color: white;
            padding: 180px 0 120px;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.8rem;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            max-width: 650px;
            opacity: 0.9;
        }
        
        .hero-actions {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .spiral-container {
            position: absolute;
            top: 50%;
            right: 5%;
            transform: translateY(-50%);
            width: 500px;
            height: 500px;
            opacity: 0.1;
        }
        
        .spiral {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            border: 2px solid white;
            border-radius: 50%;
            animation: spiralRotate 40s linear infinite;
        }
        
        .spiral:nth-child(2) {
            width: 80%;
            height: 80%;
            animation-duration: 35s;
            animation-direction: reverse;
        }
        
        .spiral:nth-child(3) {
            width: 60%;
            height: 60%;
            animation-duration: 30s;
        }
        
        .spiral:nth-child(4) {
            width: 40%;
            height: 40%;
            animation-duration: 25s;
            animation-direction: reverse;
        }
        
        @keyframes spiralRotate {
            from { transform: translate(-50%, -50%) rotate(0deg); }
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
        }
        
        .floating-element {
            position: absolute;
            opacity: 0.1;
            font-size: 2rem;
            color: white;
            animation: float 15s infinite linear;
        }
        
        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 20s;
        }
        
        .floating-element:nth-child(2) {
            top: 70%;
            left: 15%;
            animation-delay: 5s;
            animation-duration: 25s;
        }
        
        .floating-element:nth-child(3) {
            top: 40%;
            left: 85%;
            animation-delay: 10s;
            animation-duration: 18s;
        }
        
        .floating-element:nth-child(4) {
            top: 80%;
            left: 80%;
            animation-delay: 7s;
            animation-duration: 22s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }
        
        /* Features Section */
        .features {
            padding: 120px 0;
            background: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .section-title h2 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 25px auto 0;
            font-size: 1.1rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .feature-icon {
            width: 90px;
            height: 90px;
            background: var(--gradient-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 2.2rem;
            box-shadow: 0 10px 20px rgba(46, 139, 46, 0.2);
            transition: all 0.4s;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        /* How It Works Section */
        .how-it-works {
            padding: 120px 0;
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            position: relative;
        }
        
        .how-it-works::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%231a5d1a" fill-opacity="0.02" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
        }
        
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .step-number {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 60px;
            height: 60px;
            background: var(--gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(26, 93, 26, 0.3);
            z-index: 2;
        }
        
        .step-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 25px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .step-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 120px 0;
            background: white;
            position: relative;
        }
        
        .testimonial-slider {
            position: relative;
            overflow: hidden;
            padding: 20px 0;
        }
        
        .testimonial-track {
            display: flex;
            transition: transform 0.5s ease;
        }
        
        .testimonial-card {
            background: #f8fbf8;
            border-radius: 15px;
            padding: 40px 30px;
            margin: 0 15px;
            min-width: 100%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 25px;
            font-size: 1.1rem;
            line-height: 1.8;
            position: relative;
            padding-left: 30px;
        }
        
        .testimonial-text::before {
            content: """;
            font-family: Georgia, serif;
            font-size: 5rem;
            color: var(--primary-light);
            opacity: 0.2;
            position: absolute;
            top: -20px;
            left: -10px;
            line-height: 1;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .author-info h5 {
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .author-info p {
            color: #666;
            margin: 0;
        }
        
        .slider-nav {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 10px;
        }
        
        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .slider-dot.active {
            background: var(--primary);
            transform: scale(1.2);
        }
        
        /* CTA Section */
        .cta-section {
            padding: 120px 0;
            background: var(--gradient);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
        }
        
        .cta-section h2 {
            font-size: 2.8rem;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 40px;
            opacity: 0.9;
        }

        /* Enhanced Styles to Add to Your Existing CSS */

        /* Improved animations */
        @keyframes pulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.05); }
          100% { transform: scale(1); }
        }

        @keyframes bounce {
          0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
          40% { transform: translateY(-10px); }
          60% { transform: translateY(-5px); }
        }

        /* Enhanced hero section */
        .hero-content h1 {
          position: relative;
          animation: fadeInUp 1s ease-out;
        }

        .hero-content p {
          animation: fadeInUp 1s ease-out 0.3s both;
        }

        .hero-actions {
          animation: fadeInUp 1s ease-out 0.6s both;
        }

        /* Stats counter */
        .stats-counter {
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(10px);
          border-radius: 20px;
          padding: 30px;
          margin-top: 50px;
          animation: fadeInUp 1s ease-out 0.9s both;
        }

        .stat-item {
          text-align: center;
          padding: 15px;
        }

        .stat-number {
          font-size: 2.5rem;
          font-weight: 700;
          color: var(--secondary);
          display: block;
        }

        .stat-label {
          font-size: 0.9rem;
          opacity: 0.9;
          text-transform: uppercase;
          letter-spacing: 1px;
        }

        /* Enhanced feature cards with hover effects */
        .feature-card {
          position: relative;
          z-index: 1;
        }

        .feature-card::after {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: var(--gradient);
          opacity: 0;
          border-radius: 15px;
          z-index: -1;
          transition: opacity 0.4s;
        }

        .feature-card:hover::after {
          opacity: 0.05;
        }

        .feature-card:hover h3,
        .feature-card:hover p {
          color: white;
        }

        /* Floating action button */
        .floating-action {
          position: fixed;
          bottom: 30px;
          right: 30px;
          width: 60px;
          height: 60px;
          background: var(--gradient);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 1.5rem;
          box-shadow: 0 10px 25px rgba(26, 93, 26, 0.3);
          z-index: 1000;
          animation: bounce 2s infinite;
          transition: all 0.3s;
        }

        .floating-action:hover {
          transform: scale(1.1);
          animation: none;
        }

        /* Enhanced testimonial cards */
        .testimonial-card {
          position: relative;
          overflow: hidden;
        }

        .testimonial-card::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
          transition: left 0.5s;
        }

        .testimonial-card:hover::before {
          left: 100%;
        }

        /* Loading animation */
        .loading-spinner {
          display: inline-block;
          width: 20px;
          height: 20px;
          border: 3px solid rgba(255,255,255,.3);
          border-radius: 50%;
          border-top-color: #fff;
          animation: spin 1s ease-in-out infinite;
          margin-right: 10px;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        /* Enhanced buttons with loading states */
        .btn-loading {
          position: relative;
          color: transparent !important;
        }

        .btn-loading::after {
          content: '';
          position: absolute;
          width: 20px;
          height: 20px;
          top: 50%;
          left: 50%;
          margin-left: -10px;
          margin-top: -10px;
          border: 2px solid #ffffff;
          border-radius: 50%;
          border-top-color: transparent;
          animation: spin 0.8s linear infinite;
        }

        /* Parallax effect for background elements */
        .parallax-element {
          will-change: transform;
        }

        /* Enhanced form styles for your login/register pages */
        .form-container {
          background: white;
          border-radius: 20px;
          box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
          overflow: hidden;
        }

        .form-header {
          background: var(--gradient);
          color: white;
          padding: 40px 30px;
          text-align: center;
        }

        .form-body {
          padding: 40px 30px;
        }

        .form-control {
          border: 2px solid #e9ecef;
          border-radius: 10px;
          padding: 15px 20px;
          font-size: 1rem;
          transition: all 0.3s;
        }

        .form-control:focus {
          border-color: var(--primary);
          box-shadow: 0 0 0 0.2rem rgba(26, 93, 26, 0.25);
        }

        /* Enhanced mobile menu */
        .navbar-collapse {
          transition: all 0.3s ease;
        }

        @media (max-width: 991px) {
          .navbar-nav {
            padding: 20px 0;
          }
          
          .nav-link {
            margin: 5px 0;
            padding: 10px 15px !important;
            border-radius: 10px;
            transition: all 0.3s;
          }
          
          .nav-link:hover {
            background: rgba(26, 93, 26, 0.1);
          }
        }

        /* Print styles */
        @media print {
          .navbar, .floating-action, .hero-actions {
            display: none !important;
          }
          
          .hero {
            padding: 50px 0 !important;
            background: white !important;
            color: black !important;
          }
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 80px 0 20px;
        }
        
        .footer h5 {
            margin-bottom: 25px;
            color: var(--primary-light);
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--secondary);
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .footer-links a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .footer-links a:hover {
            color: var(--primary-light);
            padding-left: 5px;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .social-icons a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 3rem;
            }
            
            .spiral-container {
                display: none;
            }
            
            .hero {
                text-align: center;
                padding: 150px 0 100px;
            }
            
            .hero-actions {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
            
            .feature-card, .step-card {
                margin-bottom: 30px;
            }
        }

        /* News letter */
        .newsletter {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8fbf8 0%, #e8f5e8 100%);
        }

        .newsletter h3 {
            color: var(--primary);
            margin-bottom: 15px;
        }

        .newsletter p {
            color: #666;
            margin-bottom: 30px;
        }

        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s, visibility 0.5s;
        }

        .loading-screen.loaded {
            opacity: 0;
            visibility: hidden;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .loading-spinner-large {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        /* Plant Gallery Section */
        .plant-gallery {
            padding: 100px 0;
            background: white;
        }

        .gallery-slider {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            height: 500px;
        }

        .gallery-track {
            display: flex;
            transition: transform 0.5s ease;
            height: 100%;
        }

        .gallery-slide {
            min-width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .gallery-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-slide:hover img {
            transform: scale(1.05);
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 30px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-slide:hover .slide-content {
            transform: translateY(0);
        }

        .gallery-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            transform: translateY(-50%);
            z-index: 10;
        }

        .gallery-nav button {
            background: rgba(255,255,255,0.8);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .gallery-nav button:hover {
            background: white;
            transform: scale(1.1);
        }

        .gallery-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .gallery-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }

        .gallery-dot.active {
            background: var(--primary);
            transform: scale(1.2);
        }

        /* Hero Image Enhancement */
        .hero-image-container {
            position: relative;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .hero-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(26,93,26,0.3), rgba(230,179,37,0.2));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-overlay i {
            font-size: 4rem;
            color: white;
            opacity: 0.8;
        }

        /* Performance Optimizations */
        .lazy-load {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .lazy-load.loaded {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="loading-spinner-large"></div>
            <h4>CropDoctor AI</h4>
            <p>Loading agricultural intelligence...</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-seedling"></i>
                CropDoctor AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3 mt-3 mt-lg-0" href="pages/register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Enhanced Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content animate-on-scroll">
                        <h1>Your AI-Powered Crop Doctor</h1>
                        <p>Instantly diagnose plant diseases and get expert treatment suggestions. Our cutting-edge platform helps farmers, gardeners, and agricultural enthusiasts protect their crops and maximize harvest yields with 95% accuracy.</p>
                        <div class="hero-actions">
                            <a href="pages/login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i>
                                Login to Dashboard
                            </a>
                            <a href="pages/register.php" class="btn btn-secondary">
                                <i class="fas fa-rocket"></i>
                                Start Free Trial
                            </a>
                        </div>
                        
                        <!-- Stats Counter -->
                        <div class="stats-counter animate-on-scroll">
                            <div class="row text-center">
                                <div class="col-4">
                                    <span class="stat-number" data-count="95">0</span>%
                                    <div class="stat-label">Accuracy</div>
                                </div>
                                <div class="col-4">
                                    <span class="stat-number" data-count="50">0</span>k+
                                    <div class="stat-label">Diagnoses</div>
                                </div>
                                <div class="col-4">
                                    <span class="stat-number" data-count="150">0</span>+
                                    <div class="stat-label">Diseases</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container animate-on-scroll" style="animation-delay: 0.5s;">
                        <!-- Plant image with overlay -->
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='400' viewBox='0 0 400 400'><rect width='400' height='400' fill='%232e8b2e'/><path d='M200,100 C250,50 300,80 300,150 C300,220 250,250 200,300 C150,250 100,220 100,150 C100,80 150,50 200,100 Z' fill='%231a5d1a'/><circle cx='180' cy='120' r='15' fill='%23e6b325'/><circle cx='220' cy='120' r='15' fill='%23e6b325'/><path d='M200,180 Q220,200 200,220 Q180,200 200,180 Z' fill='%234a7c59'/></svg>" 
                             alt="Healthy Plant" class="lazy-load">
                        <div class="image-overlay">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Spiral Background Elements -->
        <div class="spiral-container">
            <div class="spiral"></div>
            <div class="spiral"></div>
            <div class="spiral"></div>
            <div class="spiral"></div>
        </div>
        
        <!-- Floating Elements -->
        <div class="floating-elements">
            <div class="floating-element"><i class="fas fa-leaf"></i></div>
            <div class="floating-element"><i class="fas fa-apple-alt"></i></div>
            <div class="floating-element"><i class="fas fa-seedling"></i></div>
            <div class="floating-element"><i class="fas fa-tree"></i></div>
        </div>
    </section>

    <!-- Plant Gallery Section -->
    <section class="plant-gallery" id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Plant Health Gallery</h2>
                <p>See examples of plant diseases our AI can diagnose and treat</p>
            </div>
            
            <div class="gallery-slider">
                <div class="gallery-track" id="galleryTrack">
                    <div class="gallery-slide">
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='500' viewBox='0 0 600 500'><rect width='600' height='500' fill='%23f8fbf8'/><path d='M300,150 C400,100 500,150 500,250 C500,350 400,400 300,450 C200,400 100,350 100,250 C100,150 200,100 300,150 Z' fill='%234aab4a'/><path d='M250,200 L350,200 L350,300 L250,300 Z' fill='%23dc3545' opacity='0.7'/><text x='300' y='380' font-family='Arial' font-size='24' text-anchor='middle' fill='%231a5d1a'>Tomato Blight</text></svg>" 
                             alt="Tomato Blight" class="lazy-load">
                        <div class="slide-content">
                            <h4>Tomato Blight</h4>
                            <p>Early blight on tomato leaves showing concentric rings and yellow halos</p>
                        </div>
                    </div>
                    <div class="gallery-slide">
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='500' viewBox='0 0 600 500'><rect width='600' height='500' fill='%23f8fbf8'/><path d='M300,150 C400,100 500,150 500,250 C500,350 400,400 300,450 C200,400 100,350 100,250 C100,150 200,100 300,150 Z' fill='%234aab4a'/><circle cx='350' cy='200' r='40' fill='%23ffc107' opacity='0.7'/><circle cx='250' cy='300' r='30' fill='%23ffc107' opacity='0.7'/><text x='300' y='380' font-family='Arial' font-size='24' text-anchor='middle' fill='%231a5d1a'>Powdery Mildew</text></svg>" 
                             alt="Powdery Mildew" class="lazy-load">
                        <div class="slide-content">
                            <h4>Powdery Mildew</h4>
                            <p>White powdery spots on squash leaves affecting photosynthesis</p>
                        </div>
                    </div>
                    <div class="gallery-slide">
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='500' viewBox='0 0 600 500'><rect width='600' height='500' fill='%23f8fbf8'/><path d='M300,150 C400,100 500,150 500,250 C500,350 400,400 300,450 C200,400 100,350 100,250 C100,150 200,100 300,150 Z' fill='%234aab4a'/><path d='M200,180 L400,180 L400,220 L200,220 Z' fill='%231a5d1a' opacity='0.7'/><circle cx='300' cy='300' r='50' fill='%2328a745'/><text x='300' y='380' font-family='Arial' font-size='24' text-anchor='middle' fill='%231a5d1a'>Healthy Corn</text></svg>" 
                             alt="Healthy Corn" class="lazy-load">
                        <div class="slide-content">
                            <h4>Healthy Corn Plant</h4>
                            <p>Vibrant green leaves showing optimal plant health and growth</p>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-nav">
                    <button id="galleryPrev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button id="galleryNext">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="gallery-dots">
                    <div class="gallery-dot active" data-slide="0"></div>
                    <div class="gallery-dot" data-slide="1"></div>
                    <div class="gallery-dot" data-slide="2"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose CropDoctor AI?</h2>
                <p>Our advanced AI technology combined with agricultural expertise provides the most accurate plant disease diagnosis available.</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>AI-Powered Diagnosis</h3>
                        <p>Our advanced machine learning algorithms analyze plant images with 95% accuracy to identify diseases quickly and effectively.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Instant Results</h3>
                        <p>Get diagnosis and treatment recommendations in seconds, not days. Save your crops before it's too late with our rapid analysis.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h3>Expert Advice</h3>
                        <p>Our recommendations are curated by agricultural experts with decades of experience in crop management and plant pathology.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Three simple steps to healthier crops and better yields</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="step-card animate-on-scroll">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3>Upload an Image</h3>
                        <p>Simply take a clear photo of your plant's leaf and upload it to our system. Our AI works best with close-up, well-lit images of affected areas.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="step-card animate-on-scroll">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Instant Diagnosis</h3>
                        <p>Our AI analyzes the image to identify potential diseases and pests, providing you with a quick and accurate diagnosis in seconds.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="step-card animate-on-scroll">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <h3>Receive Solutions</h3>
                        <p>Get instant, actionable advice on how to treat your plant, delivered to your dashboard and email with step-by-step guidance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Farmers Say</h2>
                <p>Hear from agricultural professionals who have transformed their crop management with CropDoctor AI</p>
            </div>
            
            <div class="testimonial-slider">
                <div class="testimonial-track" id="testimonialTrack">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "CropDoctor AI saved my entire tomato harvest from blight last season. The diagnosis was incredibly accurate and the treatment plan worked perfectly. I've recommended it to all my farming neighbors and they're equally impressed with the results."
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">MJ</div>
                            <div class="author-info">
                                <h5>Maria Johnson</h5>
                                <p>Organic Farmer, California</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "As a novice gardener, I was constantly struggling with identifying plant diseases. CropDoctor AI made it so easy to diagnose issues and get professional treatment advice. My garden has never been healthier or more productive!"
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">RS</div>
                            <div class="author-info">
                                <h5>Robert Smith</h5>
                                <p>Home Gardener, Texas</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "The accuracy of this platform is truly impressive. It identified a rare fungal infection in our wheat crop that even our local agronomist missed. This technology is revolutionizing how we approach crop health management."
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">AK</div>
                            <div class="author-info">
                                <h5>Ahmed Khan</h5>
                                <p>Commercial Farmer, Kansas</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="slider-nav">
                    <div class="slider-dot active" data-slide="0"></div>
                    <div class="slider-dot" data-slide="1"></div>
                    <div class="slider-dot" data-slide="2"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="cta-content animate-on-scroll">
                        <h2>Ready to Protect Your Crops?</h2>
                        <p>Join thousands of farmers and gardeners who are already using CropDoctor AI to ensure healthy plants and bountiful harvests. Start your free trial today!</p>
                        <div class="hero-actions">
                            <a href="pages/register.php" class="btn btn-light btn-primary">Start Free Trial</a>
                            <a href="#how-it-works" class="btn btn-outline-light btn-secondary">See How It Works</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <h3>Stay Updated with Crop Health Tips</h3>
                    <p class="mb-4">Get the latest agricultural insights and platform updates delivered to your inbox.</p>
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><i class="fas fa-seedling"></i> CropDoctor AI</h5>
                    <p>Your trusted partner in agricultural health. Using cutting-edge AI to protect crops and ensure food security worldwide.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                        <li><a href="#how-it-works"><i class="fas fa-chevron-right"></i> How It Works</a></li>
                        <li><a href="#testimonials"><i class="fas fa-chevron-right"></i> Testimonials</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Disease Library</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQs</a></li>
                        <li><a href="./pages/support.php"><i class="fas fa-chevron-right"></i> Support</a></li>
                        <li><a href="./pages/dev.php"><i class="fas fa-chevron-right"></i> Developer</a></li>
                    </ul>
                </div>

<div class="col-lg-3 col-md-6 mb-4">
    <h5 class="footer-title">Contact Us</h5>
    <ul class="footer-links">
        <li><a href="mailto:remotaskfreelancer@gmail.com"><i class="fas fa-envelope"></i> remotaskfreelancer@gmail.com</a></li>
        <li><a href="tel:+254102273123"><i class="fas fa-phone"></i> +254 102 273 123</a></li>
        <li><a href="https://wa.me/254703917940" target="_blank"><i class="fab fa-whatsapp"></i> +254 703 917 940</a></li>
        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Kisii, Nyanchwa</a></li>
    </ul>
</div>


            </div>
            <div class="copyright">
                <p>&copy; 2023 CropDoctor AI. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <a href="pages/register.php" class="floating-action">
        <i class="fas fa-robot"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fast loading - hide loading screen after 2 seconds max
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loadingScreen').classList.add('loaded');
            }, 1500); // Reduced to 1.5 seconds for faster perceived loading
        });

        // If page takes longer than 2 seconds, still hide the loader
        setTimeout(function() {
            document.getElementById('loadingScreen').classList.add('loaded');
        }, 2000);

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Testimonial slider
        const testimonialTrack = document.getElementById('testimonialTrack');
        const testimonialDots = document.querySelectorAll('.slider-dot');
        let currentTestimonialSlide = 0;
        
        function goToTestimonialSlide(slideIndex) {
            currentTestimonialSlide = slideIndex;
            testimonialTrack.style.transform = `translateX(-${currentTestimonialSlide * 100}%)`;
            
            // Update dots
            testimonialDots.forEach((dot, index) => {
                if (index === currentTestimonialSlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Add click events to testimonial dots
        testimonialDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToTestimonialSlide(index);
            });
        });
        
        // Auto-advance testimonial slides
        setInterval(() => {
            currentTestimonialSlide = (currentTestimonialSlide + 1) % testimonialDots.length;
            goToTestimonialSlide(currentTestimonialSlide);
        }, 5000);
        
        // Plant Gallery Slider
        const galleryTrack = document.getElementById('galleryTrack');
        const galleryDots = document.querySelectorAll('.gallery-dot');
        const galleryPrev = document.getElementById('galleryPrev');
        const galleryNext = document.getElementById('galleryNext');
        let currentGallerySlide = 0;
        
        function goToGallerySlide(slideIndex) {
            currentGallerySlide = slideIndex;
            galleryTrack.style.transform = `translateX(-${currentGallerySlide * 100}%)`;
            
            // Update dots
            galleryDots.forEach((dot, index) => {
                if (index === currentGallerySlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Gallery navigation
        galleryPrev.addEventListener('click', () => {
            currentGallerySlide = (currentGallerySlide - 1 + galleryDots.length) % galleryDots.length;
            goToGallerySlide(currentGallerySlide);
        });
        
        galleryNext.addEventListener('click', () => {
            currentGallerySlide = (currentGallerySlide + 1) % galleryDots.length;
            goToGallerySlide(currentGallerySlide);
        });
        
        // Add click events to gallery dots
        galleryDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToGallerySlide(index);
            });
        });
        
        // Auto-advance gallery slides
        setInterval(() => {
            currentGallerySlide = (currentGallerySlide + 1) % galleryDots.length;
            goToGallerySlide(currentGallerySlide);
        }, 6000);
        
        // Animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementTop < windowHeight - 100) {
                    element.classList.add('animated');
                }
            });
        }
        
        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const duration = 2000; // 2 seconds
                const step = target / (duration / 16); // 60fps
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                // Start animation when element is in viewport
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(counter);
            });
        }
        
        // Lazy load images
        function lazyLoadImages() {
            const lazyImages = document.querySelectorAll('.lazy-load');
            
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            animateOnScroll();
            animateCounters();
            lazyLoadImages();
        });
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>
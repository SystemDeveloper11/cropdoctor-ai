<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer | CropDoctor AI</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
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
        
        /* Developer Hero Section */
        .dev-hero {
            background: var(--gradient);
            color: white;
            padding: 180px 0 120px;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .dev-hero::before {
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
        
        .dev-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .dev-hero h1 {
            font-size: 3.8rem;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .dev-hero p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            max-width: 650px;
            opacity: 0.9;
        }
        
        .dev-profile-image {
            width: 350px;
            height: 350px;
            border-radius: 50%;
            object-fit: cover;
            border: 8px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.5s ease;
        }
        
        .dev-profile-image:hover {
            transform: scale(1.05);
            border-color: rgba(255, 255, 255, 0.4);
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
        
       
       /* Enhanced About Section Styles */

.about-section {
    position: relative;
    overflow: hidden;
}

/* Background elements */
.about-background-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
}

.floating-gear {
    position: absolute;
    font-size: 4rem;
    color: rgba(26, 93, 26, 0.05);
    animation: rotate 20s infinite linear;
}

.gear-1 {
    top: 10%;
    left: 5%;
    animation-duration: 25s;
}

.gear-2 {
    bottom: 15%;
    right: 8%;
    animation-duration: 30s;
    animation-direction: reverse;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.code-bracket {
    position: absolute;
    font-size: 3rem;
    color: rgba(230, 179, 37, 0.1);
    font-family: monospace;
    font-weight: bold;
    animation: floatUpDown 8s infinite ease-in-out;
}

.bracket-1 {
    top: 25%;
    right: 10%;
    animation-delay: 0s;
}

.bracket-2 {
    bottom: 25%;
    left: 12%;
    animation-delay: 4s;
}

@keyframes floatUpDown {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

/* Journey Timeline */
.journey-timeline {
    position: relative;
    margin-bottom: 60px;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 40px;
    display: flex;
    align-items: flex-start;
}

.timeline-marker {
    position: relative;
    margin-right: 25px;
    flex-shrink: 0;
}

.marker-dot {
    width: 20px;
    height: 20px;
    background: var(--gradient);
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 0 0 3px var(--primary-light);
    position: relative;
    z-index: 2;
}

.marker-line {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: calc(100% + 20px);
    background: var(--primary-light);
    z-index: 1;
}

.timeline-item:last-child .marker-line {
    display: none;
}

.timeline-content {
    flex: 1;
    background: white;
    padding: 20px 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border-left: 4px solid var(--primary);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateX(10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.timeline-content h4 {
    color: var(--primary);
    margin-bottom: 10px;
    font-size: 1.3rem;
}

.timeline-content p {
    margin: 0;
    color: #666;
}

/* About Content */
.about-content {
    position: relative;
}

.intro-paragraph {
    font-size: 1.2rem;
    line-height: 1.8;
    margin-bottom: 40px;
    text-align: center;
}

.text-highlight {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
}

.experience-counter {
    font-weight: 700;
    color: var(--secondary);
    position: relative;
}

.experience-counter::after {
    content: '+';
    position: absolute;
    right: -12px;
}

/* Skills Showcase */
.skills-showcase {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.skill-category {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.skill-category:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.skill-category h5 {
    color: var(--primary);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.2rem;
}

.skill-category h5 i {
    color: var(--secondary);
}

.skill-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-tag {
    background: rgba(26, 93, 26, 0.1);
    color: var(--primary);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: default;
}

.skill-tag:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

/* Philosophy Card */
.philosophy-card {
    background: linear-gradient(135deg, #f8fbf8 0%, #e8f5e8 100%);
    border-radius: 20px;
    padding: 40px;
    margin: 50px 0;
    display: flex;
    align-items: flex-start;
    gap: 30px;
    position: relative;
    overflow: hidden;
    border-left: 5px solid var(--secondary);
}

.philosophy-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.3)"/></svg>');
    background-size: cover;
}

.philosophy-icon {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    position: relative;
    z-index: 2;
    box-shadow: 0 10px 25px rgba(26, 93, 26, 0.2);
}

.philosophy-content {
    flex: 1;
    position: relative;
    z-index: 2;
}

.philosophy-content h4 {
    color: var(--primary);
    margin-bottom: 15px;
    font-size: 1.5rem;
}

.philosophy-highlight {
    color: var(--primary);
    font-weight: 600;
    position: relative;
}

.philosophy-highlight::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--secondary);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.philosophy-card:hover .philosophy-highlight::after {
    transform: scaleX(1);
}

.philosophy-principles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 25px;
}

.principle {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary);
    font-weight: 500;
}

.principle i {
    color: var(--secondary);
}

/* Passion Section */
.passion-section {
    margin: 60px 0 40px;
}

.passion-section h4 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 40px;
    font-size: 1.8rem;
}

.passion-items {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.passion-item {
    text-align: center;
    padding: 30px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.passion-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(26, 93, 26, 0.05), transparent);
    transition: left 0.5s;
}

.passion-item:hover::before {
    left: 100%;
}

.passion-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.passion-icon {
    width: 70px;
    height: 70px;
    background: var(--gradient-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 1.8rem;
    transition: all 0.3s ease;
}

.passion-item:hover .passion-icon {
    transform: scale(1.1) rotate(5deg);
}

.passion-content h5 {
    color: var(--primary);
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.passion-content p {
    color: #666;
    margin: 0;
    font-size: 0.95rem;
}

/* About CTA */
.about-cta {
    text-align: center;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    padding: 40px;
    border-radius: 20px;
    margin-top: 50px;
    position: relative;
    overflow: hidden;
}

.about-cta::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,100 L0,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: cover;
}

.about-cta p {
    font-size: 1.3rem;
    margin-bottom: 25px;
    position: relative;
    z-index: 2;
}

.about-cta p span {
    font-weight: 600;
    color: var(--secondary);
}

.about-cta .btn {
    position: relative;
    z-index: 2;
    background: white;
    color: var(--primary);
    border: none;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.about-cta .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .journey-timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        margin-right: 20px;
    }
    
    .philosophy-card {
        flex-direction: column;
        text-align: center;
        padding: 30px 25px;
    }
    
    .philosophy-icon {
        margin: 0 auto 20px;
    }
    
    .skills-showcase {
        grid-template-columns: 1fr;
    }
    
    .passion-items {
        grid-template-columns: 1fr;
    }
    
    .about-cta {
        padding: 30px 20px;
    }
    
    .about-cta p {
        font-size: 1.1rem;
    }
}

/* Animation delays for staggered effects */
.timeline-item:nth-child(1) { animation-delay: 0.1s; }
.timeline-item:nth-child(2) { animation-delay: 0.3s; }
.timeline-item:nth-child(3) { animation-delay: 0.5s; }

.skill-category:nth-child(1) { animation-delay: 0.2s; }
.skill-category:nth-child(2) { animation-delay: 0.4s; }

.passion-item:nth-child(1) { animation-delay: 0.1s; }
.passion-item:nth-child(2) { animation-delay: 0.3s; }
.passion-item:nth-child(3) { animation-delay: 0.5s; }
        
        /* Skills Section */
        .skills-section {
            padding: 120px 0;
            background: linear-gradient(to bottom, #f8fbf8 0%, #ffffff 100%);
            position: relative;
        }
        
        .skills-section::before {
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
        
        .skill-card {
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
        
        .skill-card::before {
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
        
        .skill-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .skill-card:hover::before {
            transform: scaleX(1);
        }
        
        .skill-icon {
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
        
        .skill-card:hover .skill-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .skill-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        /* Projects Section */
        .projects-section {
            padding: 120px 0;
            background: white;
        }
        
        .project-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s;
            height: 100%;
            margin-bottom: 30px;
        }
        
        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .project-image {
            height: 200px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .project-content {
            padding: 25px;
        }
        
        .project-content h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .project-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }
        
        .project-tag {
            background: rgba(26, 93, 26, 0.1);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Contact Section */
        .contact-section {
            padding: 120px 0;
            background: linear-gradient(to bottom, #f8fbf8 0%, #e8f5e8 100%);
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        
        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .contact-details h4 {
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .social-link {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(26, 93, 26, 0.3);
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
            .dev-hero h1 {
                font-size: 3rem;
            }
            
            .dev-hero {
                text-align: center;
                padding: 150px 0 100px;
            }
        }
        
        @media (max-width: 768px) {
            .dev-hero h1 {
                font-size: 2.5rem;
            }
            
            .dev-hero p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
            
            .skill-card, .project-card {
                margin-bottom: 30px;
            }
            
            .dev-profile-image {
                width: 250px;
                height: 250px;
            }
        }

        /* Enhanced Developer Hero Section Styles */

.dev-hero {
    position: relative;
    overflow: hidden;
}

/* Animated background elements */
.hero-background-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
}

.floating-code-icon,
.floating-leaf-icon,
.floating-ai-icon,
.floating-db-icon {
    position: absolute;
    font-size: 2.5rem;
    color: rgba(255, 255, 255, 0.1);
    animation: floatRandom 15s infinite linear;
}

.floating-code-icon {
    top: 15%;
    left: 10%;
    animation-delay: 0s;
}

.floating-leaf-icon {
    top: 70%;
    left: 15%;
    animation-delay: 3s;
    animation-duration: 18s;
}

.floating-ai-icon {
    top: 40%;
    left: 85%;
    animation-delay: 6s;
    animation-duration: 20s;
}

.floating-db-icon {
    top: 80%;
    left: 80%;
    animation-delay: 9s;
    animation-duration: 22s;
}

@keyframes floatRandom {
    0% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(20px, -15px) rotate(90deg); }
    50% { transform: translate(-10px, 20px) rotate(180deg); }
    75% { transform: translate(15px, 10px) rotate(270deg); }
    100% { transform: translate(0, 0) rotate(360deg); }
}

/* Pulse dots animation */
.pulse-dots {
    position: absolute;
    width: 100%;
    height: 100%;
}

.pulse-dot {
    position: absolute;
    border-radius: 50%;
    background: rgba(230, 179, 37, 0.3);
    animation: pulse 3s infinite;
}

.dot-1 {
    width: 20px;
    height: 20px;
    top: 20%;
    left: 5%;
    animation-delay: 0s;
}

.dot-2 {
    width: 15px;
    height: 15px;
    top: 60%;
    left: 90%;
    animation-delay: 1s;
}

.dot-3 {
    width: 25px;
    height: 25px;
    top: 80%;
    left: 10%;
    animation-delay: 2s;
}

@keyframes pulse {
    0% { transform: scale(0.5); opacity: 0.7; }
    50% { transform: scale(1.2); opacity: 0.3; }
    100% { transform: scale(0.5); opacity: 0.7; }
}

/* Developer intro */
.developer-intro {
    margin-bottom: 30px;
}

.greeting-text {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.8);
    animation: fadeInUp 0.8s ease-out;
}

.developer-name {
    font-size: 3.5rem;
    margin-bottom: 15px;
    font-weight: 700;
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.name-highlight {
    background: linear-gradient(135deg, #e6b325 0%, #ffd166 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
}

.name-highlight::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, transparent, #e6b325, transparent);
    animation: pulseLine 2s infinite;
}

@keyframes pulseLine {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.developer-title {
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 20px;
    animation: fadeInUp 0.8s ease-out 0.4s both;
    display: flex;
    align-items: center;
}

.title-text {
    margin-right: 5px;
}

.title-cursor {
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

/* Mission statement */
.mission-statement {
    max-width: 700px;
    margin-bottom: 40px;
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.mission-statement p {
    font-size: 1.3rem;
    line-height: 1.6;
}

.highlight {
    color: #e6b325;
    font-weight: 600;
    position: relative;
}

.highlight::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: #e6b325;
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s ease;
}

.mission-statement:hover .highlight::after {
    transform: scaleX(1);
    transform-origin: left;
}

/* Specialties */
.specialties-container {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    flex-wrap: wrap;
    animation: fadeInUp 0.8s ease-out 0.8s both;
}

.specialty-item {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 12px 20px;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.specialty-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-5px);
}

.specialty-icon {
    width: 40px;
    height: 40px;
    background: var(--gradient-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    color: white;
    font-size: 1.2rem;
}

/* Hero actions */
.hero-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 50px;
    flex-wrap: wrap;
    animation: fadeInUp 0.8s ease-out 1s both;
}

.btn-with-icon {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--gradient);
    border: none;
    color: white;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(26, 93, 26, 0.3);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    color: var(--primary);
}

.btn-secondary:hover {
    background: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.btn-outline-light {
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.7);
    color: white;
}

.btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-3px);
}

/* Quick stats */
.quick-stats {
    display: flex;
    gap: 30px;
    animation: fadeInUp 0.8s ease-out 1.2s both;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #e6b325;
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Profile section enhancements */
.profile-container {
    position: relative;
}

.profile-frame {
    position: relative;
    display: inline-block;
}

.dev-profile-image {
    width: 350px;
    height: 350px;
    border-radius: 50%;
    object-fit: cover;
    border: 8px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    transition: all 0.5s ease;
    position: relative;
    z-index: 2;
}

.dev-profile-image:hover {
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.4);
}

.profile-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(230, 179, 37, 0.3) 0%, transparent 70%);
    animation: pulseGlow 3s infinite alternate;
    z-index: 1;
}

@keyframes pulseGlow {
    0% { opacity: 0.5; transform: translate(-50%, -50%) scale(0.95); }
    100% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.05); }
}

/* Tech badges */
.tech-badge {
    position: absolute;
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    animation: floatBadge 6s infinite ease-in-out;
    z-index: 3;
}

.badge-1 {
    top: 20%;
    right: 10%;
    color: #f7df1e; /* JS yellow */
    animation-delay: 0s;
}

.badge-2 {
    top: 60%;
    right: 5%;
    color: #3776ab; /* Python blue */
    animation-delay: 1.5s;
}

.badge-3 {
    bottom: 20%;
    left: 10%;
    color: var(--primary);
    animation-delay: 3s;
}

.badge-4 {
    top: 10%;
    left: 15%;
    color: #336791; /* Database blue */
    animation-delay: 4.5s;
}

@keyframes floatBadge {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(5deg); }
}

/* Scroll indicator */
.scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    animation: bounce 2s infinite;
}

.scroll-text {
    font-size: 0.9rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.scroll-arrow i {
    font-size: 1.2rem;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .developer-name {
        font-size: 2.8rem;
    }
    
    .dev-profile-image {
        width: 280px;
        height: 280px;
    }
    
    .profile-glow {
        width: 310px;
        height: 310px;
    }
    
    .tech-badge {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .specialties-container {
        justify-content: center;
    }
    
    .quick-stats {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .developer-name {
        font-size: 2.3rem;
    }
    
    .developer-title {
        font-size: 1.2rem;
    }
    
    .mission-statement p {
        font-size: 1.1rem;
    }
    
    .dev-profile-image {
        width: 250px;
        height: 250px;
    }
    
    .profile-glow {
        width: 280px;
        height: 280px;
    }
    
    .hero-actions {
        justify-content: center;
    }
    
    .specialty-item {
        padding: 10px 15px;
    }
}
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-seedling"></i>
                CropDoctor AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dev.php">Developer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3 mt-3 mt-lg-0" href="register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


<!-- Developer Hero Section -->
<section class="dev-hero">
    <!-- Animated background elements -->
    <div class="hero-background-elements">
        <div class="floating-code-icon"><i class="fas fa-code"></i></div>
        <div class="floating-leaf-icon"><i class="fas fa-leaf"></i></div>
        <div class="floating-ai-icon"><i class="fas fa-robot"></i></div>
        <div class="floating-db-icon"><i class="fas fa-database"></i></div>
        <div class="pulse-dots">
            <div class="pulse-dot dot-1"></div>
            <div class="pulse-dot dot-2"></div>
            <div class="pulse-dot dot-3"></div>
        </div>
    </div>
    
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="dev-hero-content animate-on-scroll">
                    <!-- Developer intro with typing animation -->
                    <div class="developer-intro">
                        <div class="greeting-text">Hello, I'm</div>
                        <h1 class="developer-name">
                            <span class="name-highlight">Brian Mbaka</span>
                        </h1>
                        <div class="developer-title">
                            <span class="title-text">Full-Stack Developer & AI Specialist</span>
                            <span class="title-cursor">|</span>
                        </div>
                    </div>
                    
                    <!-- Mission statement -->
                    <div class="mission-statement">
                        <p>Passionate about creating <span class="highlight">innovative solutions</span> that bridge <span class="highlight">technology</span> and <span class="highlight">agriculture</span> to solve real-world problems.</p>
                    </div>
                    
                    <!-- Key specialties -->
                    <div class="specialties-container">
                        <div class="specialty-item">
                            <div class="specialty-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <span>AI & Machine Learning</span>
                        </div>
                        <div class="specialty-item">
                            <div class="specialty-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <span>AgriTech Solutions</span>
                        </div>
                        <div class="specialty-item">
                            <div class="specialty-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <span>Full-Stack Development</span>
                        </div>
                    </div>
                    
                    <!-- Call to action buttons -->
                    <div class="hero-actions">
                        <a href="#contact" class="btn btn-primary btn-with-icon">
                            <i class="fas fa-paper-plane"></i>
                            Get In Touch
                        </a>
                        <a href="#projects" class="btn btn-secondary btn-with-icon">
                            <i class="fas fa-laptop-code"></i>
                            View Projects
                        </a>
                        <a href="#about" class="btn btn-outline-light btn-with-icon">
                            <i class="fas fa-user"></i>
                            My Story
                        </a>
                    </div>
                    
                    <!-- Quick stats -->
                    <div class="quick-stats">
                        <div class="stat-item">
                            <div class="stat-number" data-count="3">0</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-count="16">0</div>
                            <div class="stat-label">Projects Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-count="25">0</div>
                            <div class="stat-label">Happy Clients</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="profile-container animate-on-scroll" style="animation-delay: 0.5s;">
                    <!-- Developer profile image with animated frame -->
                    <div class="profile-frame">
        <img src="../assets/images/avatars/brian.jpg" 
                             alt="Brian Mbaka - Developer" class="dev-profile-image">
                        <div class="profile-glow"></div>
                    </div>
                    
                    <!-- Floating tech badges around profile -->
                    <div class="tech-badge badge-1">
                        <i class="fab fa-js-square"></i>
                    </div>
                    <div class="tech-badge badge-2">
                        <i class="fab fa-python"></i>
                    </div>
                    <div class="tech-badge badge-3">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="tech-badge badge-4">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="scroll-indicator">
        <div class="scroll-text">Scroll to explore</div>
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

        
        <!-- Floating Elements -->
        <div class="floating-elements">
            <div class="floating-element"><i class="fas fa-code"></i></div>
            <div class="floating-element"><i class="fas fa-laptop-code"></i></div>
            <div class="floating-element"><i class="fas fa-database"></i></div>
            <div class="floating-element"><i class="fas fa-robot"></i></div>
        </div>
    </section>


<!-- About Section -->
<section class="about-section" id="about">
    <!-- Background elements -->
    <div class="about-background-elements">
        <div class="floating-gear gear-1"><i class="fas fa-cog"></i></div>
        <div class="floating-gear gear-2"><i class="fas fa-cog"></i></div>
        <div class="code-bracket bracket-1">{ }</div>
        <div class="code-bracket bracket-2">&lt;/&gt;</div>
    </div>
    
    <div class="container">
        <div class="section-title">
            <h2>About Me</h2>
            <p>Discover my journey, passion, and the philosophy that drives my work</p>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Timeline journey -->
                <div class="journey-timeline animate-on-scroll">
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="marker-dot"></div>
                            <div class="marker-line"></div>
                        </div>
                        <div class="timeline-content">
                            <h4>My Journey Begins</h4>
                            <p>Started my computer science studies where I discovered my passion for programming and problem-solving.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="marker-dot"></div>
                            <div class="marker-line"></div>
                        </div>
                        <div class="timeline-content">
                            <h4>AI Exploration</h4>
                            <p>Developed a keen interest in artificial intelligence and machine learning applications.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="marker-dot"></div>
                            <div class="marker-line"></div>
                        </div>
                        <div class="timeline-content">
                            <h4>CropDoctor AI</h4>
                            <p>Combined my passion for technology with agriculture to create innovative AgriTech solutions.</p>
                        </div>
                    </div>
                </div>
                

            <!-- about me section -->

                <div class="about-content animate-on-scroll">
                    <!-- Main intro with animated text -->
                    <div class="intro-paragraph">
                        <p>Hello! I'm <span class="text-highlight">Brian Mbaka</span>, a passionate full-stack developer with <span class="experience-counter" data-count="3">0</span>+ years of experience creating innovative web applications that solve real-world problems. I specialize in building scalable, user-friendly solutions using modern technologies.</p>
                    </div>
                    
                    <!-- Interactive skills showcase -->
                    <div class="skills-showcase">
                        <div class="skill-category">
                            <h5><i class="fas fa-laptop-code"></i> Development</h5>
                            <div class="skill-tags">
                                <span class="skill-tag">JavaScript</span>
                                <span class="skill-tag">React</span>
                                <span class="skill-tag">Node.js</span>
                                <span class="skill-tag">Python</span>
                                <span class="skill-tag">PHP</span>
                            </div>
                        </div>
                        <div class="skill-category">
                            <h5><i class="fas fa-robot"></i> AI & ML</h5>
                            <div class="skill-tags">
                                <span class="skill-tag">TensorFlow</span>
                                <span class="skill-tag">Computer Vision</span>
                                <span class="skill-tag">NLP</span>
                                <span class="skill-tag">Predictive Analytics</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Philosophy card with hover effect -->
                    <div class="philosophy-card animate-on-scroll">
                        <div class="philosophy-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="philosophy-content">
                            <h4>My Development Philosophy</h4>
                            <p>I believe that technology should be <span class="philosophy-highlight">accessible</span>, <span class="philosophy-highlight">intuitive</span>, and solve <span class="philosophy-highlight">genuine problems</span>. Every line of code I write is aimed at creating solutions that make a positive impact on people's lives and the environment.</p>
                            <div class="philosophy-principles">
                                <div class="principle">
                                    <i class="fas fa-check-circle"></i>
                                    <span>User-Centric Design</span>
                                </div>
                                <div class="principle">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Sustainable Solutions</span>
                                </div>
                                <div class="principle">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Clean, Maintainable Code</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passion section with animated icons -->
                    <div class="passion-section">
                        <h4>Beyond Coding</h4>
                        <div class="passion-items">
                            <div class="passion-item">
                                <div class="passion-icon">
                                    <i class="fas fa-hands-helping"></i>
                                </div>
                                <div class="passion-content">
                                    <h5>Open Source Contribution</h5>
                                    <p>Actively contributing to community projects and sharing knowledge</p>
                                </div>
                            </div>
                            <div class="passion-item">
                                <div class="passion-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="passion-content">
                                    <h5>Technical Writing</h5>
                                    <p>Documenting experiences and creating educational content</p>
                                </div>
                            </div>
                            <div class="passion-item">
                                <div class="passion-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="passion-content">
                                    <h5>Mentoring</h5>
                                    <p>Guiding aspiring developers in their tech journey</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to action -->
                    <div class="about-cta animate-on-scroll">
                        <p>Ready to bring your ideas to life? <span>Let's create something amazing together!</span></p>
                        <a href="#contact" class="btn btn-primary">
                            <i class="fas fa-rocket"></i>
                            Start a Project
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    <!-- Skills Section -->
    <section class="skills-section" id="skills">
        <div class="container">
            <div class="section-title">
                <h2>My Skills</h2>
                <p>Technologies and tools I specialize in</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="skill-card animate-on-scroll">
                        <div class="skill-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3>Frontend Development</h3>
                        <p>HTML5, CSS3, JavaScript, React, Vue.js, Bootstrap, Tailwind CSS</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="skill-card animate-on-scroll">
                        <div class="skill-icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <h3>Backend Development</h3>
                        <p>Node.js, PHP, Python, Express.js, Laravel, Django, REST APIs</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="skill-card animate-on-scroll">
                        <div class="skill-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <h3>Database Management</h3>
                        <p>MySQL, PostgreSQL, MongoDB, Firebase, Redis, Database Design</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="skill-card animate-on-scroll">
                        <div class="skill-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>AI & Machine Learning</h3>
                        <p>TensorFlow, PyTorch, Computer Vision, NLP, Predictive Analytics</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="skill-card animate-on-scroll">
                        <div class="skill-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile Development</h3>
                        <p>React Native, Flutter, Progressive Web Apps, Responsive Design</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="skill-card animate-on-scroll">
                        <div class="skill-icon">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <h3>DevOps & Cloud</h3>
                        <p>AWS, Docker, CI/CD, Git, Linux Server Management, Security</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="projects-section" id="projects">
        <div class="container">
            <div class="section-title">
                <h2>Featured Projects</h2>
                <p>Some of the projects I've worked on</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="project-card animate-on-scroll">
                        <div class="project-image">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="project-content">
                            <h3>CropDoctor AI</h3>
                            <p>An AI-powered platform for diagnosing plant diseases and providing treatment recommendations to farmers and gardeners.</p>
                            <div class="project-tags">
                                <span class="project-tag">AI/ML</span>
                                <span class="project-tag">PHP</span>
                                <span class="project-tag">JavaScript</span>
                                <span class="project-tag">Bootstrap</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="project-card animate-on-scroll">
                        <div class="project-image">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="project-content">
                            <h3>AgriMarket Connect</h3>
                            <p>A digital marketplace connecting farmers directly with consumers, eliminating middlemen and ensuring fair prices.</p>
                            <div class="project-tags">
                                <span class="project-tag">React</span>
                                <span class="project-tag">Node.js</span>
                                <span class="project-tag">MongoDB</span>
                                <span class="project-tag">Payment Gateway</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="project-card animate-on-scroll">
                        <div class="project-image">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="project-content">
                            <h3>Smart Irrigation System</h3>
                            <p>An IoT-based solution that monitors soil moisture and automates irrigation to optimize water usage in agriculture.</p>
                            <div class="project-tags">
                                <span class="project-tag">IoT</span>
                                <span class="project-tag">Python</span>
                                <span class="project-tag">Raspberry Pi</span>
                                <span class="project-tag">Mobile App</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch</h2>
                <p>Feel free to reach out for collaborations or just to say hello</p>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="contact-card animate-on-scroll">
                        <div class="contact-info">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Email</h4>
                                <p>remotaskfreelancer@gmail.com</p>
                            </div>
                        </div>
                        <div class="contact-info">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Phone</h4>
                                <p>+254 102 273 123</p>
                            </div>
                        </div>
                        <div class="contact-info">
                            <div class="contact-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="contact-details">
                                <h4>WhatsApp</h4>
                                <p>+254 703 917 940</p>
                            </div>
                        </div>
                        <div class="contact-info">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Location</h4>
                                <p>Kisii, Nyanchwa</p>
                            </div>
                        </div>
                        
                        <div class="social-links">
                            <a href="#" class="social-link">
                                <i class="fab fa-github"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-stack-overflow"></i>
                            </a>
                        </div>
                    </div>
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
                        <li><a href="../index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="../index.php#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                        <li><a href="../index.php#how-it-works"><i class="fas fa-chevron-right"></i> How It Works</a></li>
                        <li><a href="../index.php#testimonials"><i class="fas fa-chevron-right"></i> Testimonials</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Disease Library</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQs</a></li>
                        <li><a href="support.php"><i class="fas fa-chevron-right"></i> Support</a></li>
                        <li><a href="dev.php"><i class="fas fa-chevron-right"></i> Developer</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Us</h5>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            animateOnScroll();
        });

        // Counter animation for stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const duration = 2000;
        const step = target / (duration / 16);
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    animateCounters();
});

// Experience counter animation
function animateExperienceCounter() {
    const counter = document.querySelector('.experience-counter');
    const target = parseInt(counter.getAttribute('data-count'));
    let current = 0;
    const duration = 2000;
    const step = target / (duration / 16);
    
    const updateCounter = () => {
        current += step;
        if (current < target) {
            counter.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
        } else {
            counter.textContent = target;
        }
    };
    
    // Start when element is in viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateCounter();
                observer.unobserve(entry.target);
            }
        });
    });
    
    observer.observe(counter);
}

// Initialize animations
document.addEventListener('DOMContentLoaded', function() {
    animateExperienceCounter();
    
    // Add hover effects to skill tags
    const skillTags = document.querySelectorAll('.skill-tag');
    skillTags.forEach(tag => {
        tag.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.05)';
        });
        
        tag.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>
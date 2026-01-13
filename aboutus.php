<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | JC Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --secondary: #1a1a1a;
            --light: #f8f9fa;
            --dark: #343a40;
            --text: #555;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            background-color: white;
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles - 与其他页面相同 */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 0;
            margin-left: 30px;
            margin-right: 50px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            color: orange;
            font-family: "Times New Roman", Times, serif;
        }
                
        .logo i {
            margin-right: 10px;
            font-size: 32px;
            color: orange;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            font-family: "Times New Roman", Times, serif;
        }
                
        .nav-links li {
            margin-left: 30px;
            position: relative;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: bold;
            font-size: 16px;
            transition: color 0.3s;
            padding: 5px 0;
            font-family: "Times New Roman", Times, serif;
        }
                
        .nav-links a:hover {
            color: orange;
        }
                
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: orange;
            transition: width 0.3s;
        }
                
        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }
        
        .active {
            color: orange;
        }
        
        /* Cart button - 与contactus.php相同 */
        .cart-btn {
            display: inline-flex;
            align-items: center;
            background-color: transparent;
            color: var(--secondary);
            border: 2px solid orange;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            font-family: "Times New Roman", Times, serif;
        }
        
        .cart-btn:hover {
            background-color: orange;
            color: white;
            transform: translateY(-2px);
        }
        
        .cart-btn i {
            margin-right: 8px;
        }
        
        /* Hero Banner */
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            height: 60vh;
            display: flex;
            align-items: center;
            text-align: center;
            color: white;
            margin-top: 100px;
        }
        
        .about-hero-content {
            width: 100%;
            padding: 0 20px;
        }
        
        .about-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            line-height: 1.2;
            font-weight: bold;
            color: white;
            font-family: "Times New Roman", Times, serif;
        }
        
        .about-hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.9);
            font-family: "Times New Roman", Times, serif;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Our Story Section */
        .our-story {
            padding: 80px 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background-color: orange;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .story-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .story-image {
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .story-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }
        
        .story-image img:hover {
            transform: scale(1.05);
        }
        
        .story-text h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--secondary);
            font-family: "Times New Roman", Times, serif;
        }
        
        .story-text p {
            margin-bottom: 20px;
            line-height: 1.8;
            font-size: 1.05rem;
        }
        
        .story-features {
            margin-top: 30px;
        }
        
        .story-feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .story-feature i {
            color: orange;
            margin-right: 15px;
            font-size: 1.3rem;
        }
        
        .story-feature span {
            font-size: 1rem;
        }
        
        /* Our Values Section */
        .our-values {
            padding: 80px 0;
            background-color: var(--light);
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .value-card {
            background: white;
            padding: 40px 30px;
            border-radius: 0;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .value-icon {
            font-size: 50px;
            color: orange;
            margin-bottom: 20px;
        }
        
        .value-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--secondary);
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
        
        .value-card p {
            color: var(--text);
            font-size: 1rem;
        }
        
        /* Our Team Section */
        .our-team {
            padding: 80px 0;
            background-color: white;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .team-member {
            background: white;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        
        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .member-image {
            height: 250px;
            overflow: hidden;
        }
        
        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .team-member:hover .member-image img {
            transform: scale(1.1);
        }
        
        .member-info {
            padding: 25px 20px;
            text-align: center;
        }
        
        .member-info h3 {
            font-size: 1.4rem;
            margin-bottom: 5px;
            color: var(--secondary);
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
        
        .member-info .position {
            color: orange;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .member-info p {
            color: var(--text);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .member-social {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .member-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: #f5f5f5;
            border-radius: 50%;
            color: var(--secondary);
            transition: all 0.3s;
        }
        
        .member-social a:hover {
            background-color: orange;
            color: white;
            transform: translateY(-3px);
        }
        
        /* Awards Section */
        .awards {
            padding: 80px 0;
            background-color: var(--light);
        }
        
        .awards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .award-item {
            background: white;
            padding: 30px 20px;
            border-radius: 0;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        
        .award-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .award-icon {
            font-size: 40px;
            color: orange;
            margin-bottom: 15px;
        }
        
        .award-item h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--secondary);
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
        
        .award-item p {
            color: var(--text);
            font-size: 0.9rem;
        }
        
        /* Footer */
        footer {
            background-color: var(--secondary);
            color: white;
            padding: 60px 0 20px 0;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 2px;
            background-color: orange;
            bottom: 0;
            left: 0;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .footer-links i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
            font-family: "Times New Roman", Times, serif;
        }
        
        .footer-links a:hover {
            color: orange;
            padding-left: 5px;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            font-family: "Times New Roman", Times, serif;
        }
        
        .social-icons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: orange;
            color: var(--secondary);
            transform: translateY(-3px);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .story-content {
                grid-template-columns: 1fr;
            }
            
            .about-hero h1 {
                font-size: 3rem;
            }
            
            .nav-links {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .about-hero {
                margin-top: 120px;
            }
            
            .about-hero h1 {
                font-size: 2.5rem;
            }
            
            .about-hero p {
                font-size: 1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .navbar {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 10px;
            }
            
            .nav-links li {
                margin: 5px 10px;
            }
            
            .team-grid,
            .values-grid,
            .awards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .about-hero h1 {
                font-size: 2rem;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .logo {
                font-size: 22px;
            }
            
            .logo i {
                font-size: 24px;
            }
            
            .team-grid,
            .values-grid,
            .awards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="homepage.html" class="logo">
                    <i class="fas fa-utensils"></i>
                    JC Restaurant
                </a>
                <ul class="nav-links">
                    <li><a href="homepage.html">HOME</a></li>
                    <li><a href="aboutus.php" class="active">ABOUT</a></li>
                    <li><a href="menuPage.php">MENU</a></li>
                    <li><a href="contactus.php">CONTACT</a></li>
                    <li>
                        <a href="/jc_restaurant/customer_profile/edit-profile.php">
                            EDIT PROFILE
                        </a>
                    </li>
                    <li>
                        <a href="#" class="cart-btn">
                            <i class="fa-solid fa-cart-shopping"></i>My Cart
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Banner -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <h1>Our Story</h1>
                <p>For over a decade, JC Restaurant has been redefining fine dining with our commitment to excellence, innovation, and unforgettable culinary experiences.</p>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="our-story">
        <div class="container">
            <div class="section-title">
                <h2>Our Journey</h2>
            </div>
            <div class="story-content">
                <div class="story-image">
                    <img src="https://images.unsplash.com/photo-1552566626-52f8b828add9?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Restaurant Founder">
                </div>
                <div class="story-text">
                    <h3>A Legacy of Culinary Excellence</h3>
                    <p>Founded in 2010 by Chef James Collins, JC Restaurant began as a small bistro with big dreams. Chef Collins, with over 20 years of culinary experience in Michelin-starred restaurants across Europe, envisioned a place where exceptional food meets warm hospitality.</p>
                    <p>What started as a 30-seat establishment has now grown into one of the city's most celebrated dining destinations. Our journey has been guided by a simple philosophy: source the finest ingredients, treat them with respect, and create dishes that tell a story.</p>
                    <div class="story-features">
                        <div class="story-feature">
                            <i class="fas fa-check"></i>
                            <span>Established in 2010 with a passion for fine dining</span>
                        </div>
                        <div class="story-feature">
                            <i class="fas fa-check"></i>
                            <span>Led by award-winning Chef James Collins</span>
                        </div>
                        <div class="story-feature">
                            <i class="fas fa-check"></i>
                            <span>Committed to sustainable and local sourcing</span>
                        </div>
                        <div class="story-feature">
                            <i class="fas fa-check"></i>
                            <span>Recipient of multiple culinary awards</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="our-values">
        <div class="container">
            <div class="section-title">
                <h2>Our Values</h2>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h3>Sustainability</h3>
                    <p>We believe in responsible sourcing and minimizing our environmental impact through partnerships with local farms and sustainable producers.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Passion</h3>
                    <p>Every dish is crafted with love and dedication. Our team's passion for culinary excellence shines through in every plate we serve.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community</h3>
                    <p>We're proud to be part of our local community, supporting local businesses and creating a welcoming space for all our guests.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team Section -->
    <section class="our-team">
        <div class="container">
            <div class="section-title">
                <h2>Meet Our Team</h2>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Chef James Collins">
                    </div>
                    <div class="member-info">
                        <h3>James Collins</h3>
                        <p class="position">Executive Chef & Founder</p>
                        <p>With 25 years of culinary experience, Chef Collins leads our kitchen with creativity and precision.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1595475038784-bbe439ff41e6?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Sarah Johnson">
                    </div>
                    <div class="member-info">
                        <h3>Sarah Johnson</h3>
                        <p class="position">Head Pastry Chef</p>
                        <p>Specializing in artisanal desserts, Sarah brings sweetness and artistry to every creation.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1581299894007-aaa50297cf16?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Michael Chen">
                    </div>
                    <div class="member-info">
                        <h3>Michael Chen</h3>
                        <p class="position">Sommelier</p>
                        <p>Michael curates our extensive wine selection, ensuring perfect pairings for every dish.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Emily Rodriguez">
                    </div>
                    <div class="member-info">
                        <h3>Emily Rodriguez</h3>
                        <p class="position">Restaurant Manager</p>
                        <p>Emily ensures every guest experiences exceptional service from arrival to departure.</p>
                        <div class="member-social">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Awards Section -->
    <section class="awards">
        <div class="container">
            <div class="section-title">
                <h2>Awards & Recognition</h2>
            </div>
            <div class="awards-grid">
                <div class="award-item">
                    <div class="award-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Michelin Star</h3>
                    <p>Awarded 2022 for culinary excellence and innovation</p>
                </div>
                <div class="award-item">
                    <div class="award-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Best Fine Dining</h3>
                    <p>City Dining Awards 2023 - Winner</p>
                </div>
                <div class="award-item">
                    <div class="award-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Sustainability Award</h3>
                    <p>Green Restaurant Association 2022</p>
                </div>
                <div class="award-item">
                    <div class="award-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>Chef of the Year</h3>
                    <p>James Collins - Culinary Excellence Awards 2021</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>JC Restaurant</h3>
                    <p>Experience fine dining at its best with our exquisite menu and unparalleled service.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-tripadvisor"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="homepage.html">Home</a></li>
                        <li><a href="aboutus.php">About Us</a></li>
                        <li><a href="contactus.php">Contact</a></li>
                        <li><a href="menuPage.php">Menu</a></li>
                        <li><a href="/jc_restaurant/customer_profile/edit-profile.php">Edit Profile</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i><a href="#">123 Restaurant St, Food City</a></li>
                        <li><i class="fas fa-phone"></i><a href="#">+1 234 567 8900</a></li>
                        <li><i class="fas fa-envelope"></i><a href="#">info@jcrestaurant.com</a></li>
                        <li><i class="fas fa-clock"></i><a href="#">Mon-Sun: 11AM - 11PM</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JC Restaurant. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>

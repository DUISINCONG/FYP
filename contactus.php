<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | JC Restaurant</title>
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
        
        /* Header Styles - 与homepage.html一致 */
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
        
        /* Cart button in navbar */
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
        
        .nav-links .cart-btn:hover {
            background-color: orange;
            color: white;
            transform: translateY(-2px);
        }

        .nav-links .cart-btn::after {
            display: none;
        }
        
        .cart-btn i {
            margin-right: 8px;
        }
        
        /* Page Hero Section */
        .page-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1559925393-8be0ec4767c8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            height: 60vh;
            display: flex;
            align-items: center;
            text-align: center;
            color: white;
            margin-top: 100px;
        }
        
        .page-hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
            font-weight: bold;
            color: white;
            font-family: "Times New Roman", Times, serif;
        }
        
        .page-hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.9);
            font-family: "Times New Roman", Times, serif;
        }
        
        /* Contact Info Center Section */
        .contact-center-section {
            padding: 80px 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
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
            width: 70px;
            height: 3px;
            background-color: orange;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title p {
            max-width: 600px;
            margin: 0 auto;
            color: var(--text);
            font-size: 1rem;
            font-family: "Times New Roman", Times, serif;
        }
        
        /* Centered Contact Information */
        .contact-info-center {
            max-width: 800px;
            margin: 0 auto;
            background: var(--light);
            border-radius: 0;
            border: 1px solid #e0e0e0;
            padding: 50px;
            text-align: center;
        }
        
        .contact-info-center h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: var(--secondary);
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
            position: relative;
            padding-bottom: 15px;
        }
        
        .contact-info-center h3::after {
            content: '';
            position: absolute;
            width: 70px;
            height: 2px;
            background-color: orange;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .contact-details-center {
            margin-bottom: 40px;
        }
        
        .contact-item-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        
        .contact-item-center:hover {
            transform: translateY(-5px);
        }
        
        .contact-icon-center {
            background-color: orange;
            color: white;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .contact-text-center h4 {
            color: var(--secondary);
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
            font-size: 1.3rem;
        }
        
        .contact-text-center p {
            color: var(--text);
            font-family: "Times New Roman", Times, serif;
            line-height: 1.8;
        }
        
        /* Hours Info Center */
        .hours-info-center {
            margin-top: 40px;
        }
        
        .hours-info-center h4 {
            color: var(--secondary);
            margin-bottom: 25px;
            font-family: "Times New Roman", Times, serif;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .hours-info-center h4::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 2px;
            background-color: orange;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .hours-table-center {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .hours-table-center tr {
            border-bottom: 1px solid #eee;
        }
        
        .hours-table-center td {
            padding: 12px 0;
            font-family: "Times New Roman", Times, serif;
            text-align: left;
        }
        
        .hours-table-center td:first-child {
            font-weight: bold;
            color: var(--secondary);
            width: 60%;
        }
        
        .hours-table-center td:last-child {
            text-align: right;
            width: 40%;
        }
        
        /* Map Section */
        .map-section {
            padding: 0 0 80px 0;
        }
        
        .map-container {
            height: 400px;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }
        
        iframe {
            width: 100%;
            height: 100%;
            border: none;
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
            .nav-links {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .page-hero {
                text-align: center;
                margin-top: 120px;
                height: 50vh;
            }
            
            .page-hero h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .contact-info-center {
                padding: 30px;
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
        }
        
        @media (max-width: 576px) {
            .page-hero h1 {
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
            
            .contact-info-center {
                padding: 20px;
            }
            
            .hours-table-center td {
                padding: 8px 0;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Navigation - 与homepage.html一致 -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="homepage.html" class="logo">
                    <i class="fas fa-utensils"></i>
                    JC Restaurant
                </a>
                <ul class="nav-links">
                    <li><a href="homepage.html">HOME</a></li>
                    <li><a href="aboutus.php">ABOUT</a></li>
                    <li><a href="contactus.php" class="active">CONTACT</a></li>
                    <li><a href="menuPage.php">MENU</a></li>
                    <li>
                        <a href="/jc_restaurant/customer_profile/edit-profile.php">
                            EDIT PROFILE
                        </a>
                    </li>
                    <li><a href="myorder.php">ORDER</a></li>
                    <li>
                        <a href="AddToCart.php" class="cart-btn">
                            <i class="fa-solid fa-cart-shopping"></i>My Cart
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Page Hero Section -->
    <section class="page-hero">
        <div class="container">
            <div class="page-hero-content">
                <h1>Contact Us</h1>
                <p>Have questions or feedback? We'd love to hear from you. Get in touch with our team.</p>
            </div>
        </div>
    </section>

    <!-- Centered Contact Information Section -->
    <section class="contact-center-section">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch</h2>
                <p>Feel free to reach out to us with any inquiries or reservation requests</p>
            </div>
            
            <div class="contact-info-center">
                <h3>Contact Information</h3>
                
                <div class="contact-details-center">
                    <div class="contact-item-center">
                        <div class="contact-icon-center">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text-center">
                            <h4>Our Location</h4>
                            <p>123 Restaurant Street, Food City, FC 10001</p>
                        </div>
                    </div>
                    
                    <div class="contact-item-center">
                        <div class="contact-icon-center">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text-center">
                            <h4>Phone Number</h4>
                            <p>+1 (234) 567-8900</p>
                            <p>+1 (234) 567-8901</p>
                        </div>
                    </div>
                    
                    <div class="contact-item-center">
                        <div class="contact-icon-center">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text-center">
                            <h4>Email Address</h4>
                            <p>info@jcrestaurant.com</p>
                            <p>reservations@jcrestaurant.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="hours-info-center">
                    <h4>Opening Hours</h4>
                    <table class="hours-table-center">
                        <tr>
                            <td>Monday - Thursday</td>
                            <td>11:00 AM - 10:00 PM</td>
                        </tr>
                        <tr>
                            <td>Friday - Saturday</td>
                            <td>11:00 AM - 11:00 PM</td>
                        </tr>
                        <tr>
                            <td>Sunday</td>
                            <td>11:00 AM - 9:00 PM</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.177858804427!2d-73.98784468459418!3d40.70555197933205!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a315cdf4c9b%3A0x8b934de5cae6f43!2s123%20Restaurant%20St%2C%20New%20York%2C%20NY%2010001%2C%20USA!5e0!3m2!1sen!2s!4v1629996549010!5m2!1sen!2s" allowfullscreen="" loading="lazy"></iframe>
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
                        <li><a href="myorder.php">Order</a></li>
                        <li><a href="AddToCart.php">My Cart</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i>123 Restaurant St, Food City</a></li>
                        <li><i class="fas fa-phone"></i>+1 234 567 8900</a></li>
                        <li><i class="fas fa-envelope"></i>info@jcrestaurant.com</a></li>
                        <li><i class="fas fa-clock"></i>Mon-Sun: 11AM - 11PM</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JC Restaurant. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect - 与homepage.html一致
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

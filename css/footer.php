<div style="background:red;color:white;padding:20px;">
    FOOTER TEST
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .main-footer {
    background-color: #020617;
    color: #e2e8f0;
    padding: 60px 0 20px 0;
    font-family: 'Inter', sans-serif;
    border-top: 1px solid #1e293b;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
   
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    padding: 0 20px;
}

.footer-heading {
    color: #38bdf8; 
    font-size: 1.2rem;
    margin-bottom: 20px;
    font-weight: 600;
}

.footer-logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #38bdf8;
}

.footer-about p {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #94a3b8;
}


.footer-social {
    margin-top: 20px;
    display: flex;
    gap: 15px;
}

.social-icon-link {
    color: #cbd5e1;
    font-size: 1.2rem;
    transition: color 0.3s ease;
}

.social-icon-link:hover {
    color: #38bdf8;
}


.footer-links ul {
    list-style: none;
    padding: 0;
}

.footer-links ul li {
    margin-bottom: 10px;
}

.footer-links ul li a {
    text-decoration: none;
    color: #94a3b8;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.footer-links ul li a:hover {
    color: #38bdf8;
    padding-left: 5px;
}


.footer-contact p {
    font-size: 0.9rem;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #94a3b8;
}

.footer-contact i {
    color: #38bdf8;
    width: 16px;
}

.payment-section {
    margin-top: 25px;
}

.payment-title {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.payment-methods {
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-methods img {
    height: 20px;
    background: white;
    padding: 2px 5px;
    border-radius: 3px;
}

.fpx-icon {
    background: white;
    color: #e11d48;
    font-weight: bold;
    font-size: 0.7rem;
    padding: 2px 5px;
    border-radius: 3px;
    border: 1px solid #ddd;
}


.support-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.support-form input, 
.support-form textarea {
    width: 100%;
    padding: 10px;
    background-color: #0f172a; 
    border: 1px solid #334155;
    color: #f8fafc;
    border-radius: 4px;
    font-size: 0.85rem;
    resize:none;
}


.support-form input:focus, 
.support-form textarea:focus {
    outline: none;
    border-color: #38bdf8;
}

.support-form button {
    background-color: #38bdf8;
    color: #020617;
    border: none;
    padding: 10px;
    font-weight: bold;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.3s;
}

.support-form button:hover {
    background-color: #0ea5e9;
    transform: translateY(-2px);
}


.footer-copyright {
    text-align: center;
    margin-top: 50px;
    padding-top: 20px;
    border-top: 1px solid #1e293b;
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.8;
}

.footer-copyright a {
    color: #64748b;
    text-decoration: none;
    margin: 0 5px;
}

.footer-copyright a:hover {
    color: #38bdf8;
}
</style>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-about">
            <h2 class="footer-logo">Minsoft Solution</h2>  

            <p>
                Your ultimate destination for high-performance computing. 
                We specialize in custom gaming PCs and cutting-edge hardware components. 
                <strong>Build your own PC.</strong>
            </p>

            <div class="footer-social">
                <a href="#" class="social-icon-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon-link"><i class="fab fa-twitter"></i></a> 
                <a href="#" class="social-icon-link"><i class="fab fa-tiktok"></i></a> 
            </div>
        </div>

        <div class="footer-links">
            <h3 class="footer-heading">Shop Categories</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">View Products</a></li>
                <li><a href="cart.php">My Cart</a></li>
                <li><a href="builder.php">PC Builder</a></li> 
            </ul>
        </div>

        <div class="footer-contact">
            <h3 class="footer-heading">Contact Us</h3>
            <p><i class="fas fa-map-marker-alt"></i> 16, Jalan Semi, 83000 Batu Pahat, Johor</p>
            <p><i class="fas fa-phone-alt"></i> +60 11-10349304</p>
            <p><i class="fas fa-envelope"></i> support@minsoftsolution.com</p>
            
            <div class="payment-section">
                <div class="payment-title">Secure Payment</div>
                <div class="payment-methods">
                    <img src="uploads/visa.png" alt="Visa" style="height: 22px; width: auto; object-fit: contain; vertical-align: middle;">
                    <img src="uploads/mastercard.png" alt="Mastercard" style="height: 22px; width: auto; object-fit: contain; vertical-align: middle;">
                    <div class="fpx-icon">FPX</div>
                </div>
            </div>
        </div>

        <div class="footer-support">
            <h3 class="footer-heading">Send Us a Message</h3>
            <form action="contact.php" method="POST" class="support-form">
                <input type="text" name="Sender_Name" placeholder="Your Name" required>
                <input type="email" name="Send_Email" placeholder="Your Email" required>
                <input type="text" name="Subject" placeholder="Subject" required>
                <textarea name="Message" placeholder="Message..." rows="3" required></textarea>
                <button type="submit" name="submit_contact">Send Message</button>
            </form>
        </div>
    </div>

    <div class="footer-copyright">
        <p>
            &copy; 2026 Minsoft Solutions. All Rights Reserved.
            <br>
            <a href="#">Privacy Policy</a> | <a href="#">Warranty Policy</a> | <a href="#">Shipping Info</a>
        </p>
    </div>
</footer>
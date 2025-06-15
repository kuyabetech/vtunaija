<style>
    /* Modern Banking Footer - Pure CSS */
.footer {
  background: #ffffff;
  color: #1f2937;
  padding: 3rem 0 1.5rem;
  border-top: 1px solid #e5e7eb;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.footer-container {
  width: 90%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-logo-section {
  display: flex;
  flex-direction: column;
}

.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.footer-logo img {
  height: 40px;
  border-radius: 8px;
  margin-right: 12px;
}

.footer-logo-text {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.footer-description {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.footer-social {
  display: flex;
  gap: 1rem;
}

.footer-social a {
  color: #6b7280;
  font-size: 1.2rem;
  transition: color 0.2s ease;
}

.footer-social a:hover {
  color: #2563eb;
}

.footer-contact {
  margin-top: 1rem;
  color: #6b7280;
  font-size: 0.9rem;
}

.footer-heading {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.25rem;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 0.75rem;
}

.footer-links a {
  color: #6b7280;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s ease;
}

.footer-links a:hover {
  color: #2563eb;
}

.footer-divider {
  border: none;
  border-top: 1px solid #e5e7eb;
  margin: 2rem 0;
}

.footer-bottom {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  font-size: 0.85rem;
}

.footer-copyright {
  color: #6b7280;
}

.footer-payment-methods {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.footer-payment-methods img {
  height: 20px;
  opacity: 0.8;
  transition: opacity 0.2s ease;
}

.footer-payment-methods img:hover {
  opacity: 1;
}

/* Back to Top Button */
.back-to-top {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
}

.back-to-top.visible {
  opacity: 1;
  visibility: visible;
}

.back-to-top:hover {
  background: #1d4ed8;
  transform: translateY(-2px);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .footer-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
  }
  
  .footer-payment-methods {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .footer-grid {
    grid-template-columns: 1fr;
  }
}
   </style>
<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <div class="footer-logo-section">
        <div class="footer-logo">
          <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo htmlspecialchars($siteName); ?>">
          <span class="footer-logo-text"><?php echo htmlspecialchars($siteName); ?></span>
        </div>
        <p class="footer-description">Your trusted platform for all VTU services in Nigeria. Fast, reliable, and secure transactions.</p>
        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <div class="footer-contact">
          <strong>Contact Address:</strong> <?php echo isset($contactAddress) && $contactAddress ? htmlspecialchars($contactAddress) : 'No. 1, Example Street, Lagos, Nigeria'; ?>
        </div>
      </div>
      
      <div>
        <h3 class="footer-heading">Services</h3>
        <ul class="footer-links">
          <li><a href="/airtime">Airtime Top-up</a></li>
          <li><a href="/data">Data Bundles</a></li>
          <li><a href="/bills">Bills Payment</a></li>
          <li><a href="/cable">Cable TV</a></li>
          <li><a href="/electricity">Electricity</a></li>
        </ul>
      </div>
      
      <div>
        <h3 class="footer-heading">Company</h3>
        <ul class="footer-links">
          <li><a href="/about">About Us</a></li>
          <li><a href="/contact">Contact Us</a></li>
          <li><a href="/blog">Blog</a></li>
          <li><a href="/careers">Careers</a></li>
          <li><a href="/press">Press</a></li>
        </ul>
      </div>
      
      <div>
        <h3 class="footer-heading">Legal</h3>
        <ul class="footer-links">
          <li><a href="/terms">Terms of Service</a></li>
          <li><a href="/privacy">Privacy Policy</a></li>
          <li><a href="/refund">Refund Policy</a></li>
          <li><a href="/security">Security</a></li>
          <li><a href="/compliance">Compliance</a></li>
        </ul>
      </div>
      
      <div>
        <h3 class="footer-heading">Support</h3>
        <ul class="footer-links">
          <li><a href="/faq">FAQ</a></li>
          <li><a href="/help">Help Center</a></li>
          <li><a href="/live-chat">Live Chat</a></li>
          <li><a href="/status">System Status</a></li>
          <li><a href="/feedback">Feedback</a></li>
        </ul>
      </div>
    </div>
    
    <hr class="footer-divider">
    
    <div class="footer-bottom">
      <div class="footer-copyright">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. All rights reserved.
      </div>
      <div class="footer-payment-methods">
        <span>Payment Methods:</span>
        <img src="assets/images/payments/visa.png" alt="Visa">
        <img src="assets/images/payments/mastercard.png" alt="Mastercard">
        <img src="assets/images/payments/verve.png" alt="Verve">
        <img src="assets/images/payments/paystack.png" alt="Paystack">
        <img src="assets/images/payments/flutter.png" alt="Flutterwave">
      </div>
    </div>
  </div>
</footer>

<button class="back-to-top" id="backToTop">
  <i class="fas fa-arrow-up"></i>
</button>

<script>
window.addEventListener('scroll', function() {
  var btn = document.getElementById('backToTop');
  if(window.scrollY > 200) {
    btn.classList.add('visible');
  } else {
    btn.classList.remove('visible');
  }
});

document.getElementById('backToTop').onclick = function() {
  window.scrollTo({top:0,behavior:'smooth'});
};
</script>
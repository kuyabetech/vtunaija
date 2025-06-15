<?php
require_once 'includes/header.php';
?>
<!-- Loader Spinner -->

<style>
:root {
  --primary: #2563eb;
  --primary-light: #3b82f6;
  --secondary: #059669;
  --accent: #7c3aed;
  --text: #1f2937;
  --text-light: #6b7280;
  --bg: #f9fafb;
  --card-bg: #ffffff;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --border: #e5e7eb;
}
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: var(--bg);
  color: var(--text);
  margin: 0;
  padding: 0;
  line-height: 1.5;
}
.hero-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: center;
  margin-bottom: 2rem;
}
@media (max-width: 900px) {
  .hero-section {
    grid-template-columns: 1fr;
    text-align: center;
  }
}
.hero-title {
  color: var(--primary);
  font-family: 'Inter', Arial, sans-serif;
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}
.hero-lead {
  color: var(--text-light);
  font-size: 1.15rem;
  margin-bottom: 1.5rem;
}
.hero-btns .btn {
  margin-right: 1rem;
  margin-bottom: 0.5rem;
}
.hero-img {
  max-height: 340px;
  border-radius: 16px;
  box-shadow: 0 0 24px var(--primary);
  background: #fff;
  padding: 0.5rem;
}
.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}
.card {
  background: var(--card-bg);
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.04);
  border: none;
  padding: 1.5rem 1rem;
  text-align: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
}
.card-title {
  color: var(--primary);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}
.card-text {
  color: var(--text-light);
  font-size: 0.98rem;
}
.arcade-banner {
  background: linear-gradient(90deg, var(--primary) 60%, var(--accent) 100%);
  border-radius: 1.5rem;
  padding: 1.5rem 1rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 16px var(--primary);
  color: #fff;
  text-align: center;
}
.arcade-banner h2 {
  font-size: 1.3rem;
  margin-bottom: 0.5rem;
}
.arcade-banner a {
  color: var(--primary);
  font-weight: 700;
}
.arcade-table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 8px;
  overflow: hidden;
  background: var(--card-bg);
  color: var(--text);
  font-size: 0.95rem;
}
.arcade-table th, .arcade-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border);
}
.arcade-table th {
  background: #f3f4f6;
  color: var(--text);
  font-weight: 600;
}
.arcade-table tr:last-child td {
  border-bottom: none;
}
.arcade-table tr:nth-child(even) {
  background: #f9fafb;
}
.badge-success {
  background: #ecfdf5;
  color: var(--success);
  border-radius: 6px;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.badge-danger {
  background: #fee2e2;
  color: var(--danger);
  border-radius: 6px;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.badge-warning {
  background: #fef3c7;
  color: var(--warning);
  border-radius: 6px;
  padding: 0.35rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.accordion-button {
  background: #f3f4f6;
  color: var(--primary);
  font-weight: 600;
  font-size: 1rem;
  border: none;
  border-radius: 8px 8px 0 0;
  box-shadow: none;
}
.accordion-button:not(.collapsed) {
  background: var(--primary);
  color: #fff;
}
.accordion-item {
  border: 1px solid var(--border);
  border-radius: 8px;
  margin-bottom: 1rem;
  background: var(--card-bg);
}
.accordion-body {
  background: #fff;
  color: var(--text);
  border-radius: 0 0 8px 8px;
}
@media (max-width: 900px) {
  .dashboard-cards { grid-template-columns: 1fr; }
  .hero-section { grid-template-columns: 1fr; }
}
</style>

<div class="container py-5">
    <div class="hero-section">
        <div>
            <h1 class="hero-title">Welcome to VTUNaija</h1>
            <p class="hero-lead">Your trusted platform for instant airtime, data, and bill payments in Nigeria. Enjoy fast transactions, secure wallet, and amazing bonuses!</p>
            <div class="hero-btns">
                <a href="register.php" class="btn btn-primary btn-powerup">Get Started</a>
                <a href="login.php" class="btn btn-outline-primary">Login</a>
            </div>
        </div>
        <div>
            <img src="assets/images/hero.png" alt="VTU Nigeria" class="hero-img img-fluid">
        </div>
    </div>
    <div class="dashboard-cards">
        <div class="card">
            <h5 class="card-title"><i class="fas fa-bolt"></i> Instant Airtime</h5>
            <p class="card-text">Buy airtime for all networks instantly, anytime, anywhere.</p>
        </div>
        <div class="card">
            <h5 class="card-title"><i class="fas fa-database"></i> Affordable Data</h5>
            <p class="card-text">Get affordable data bundles for MTN, Glo, Airtel, and 9mobile.</p>
        </div>
        <div class="card">
            <h5 class="card-title"><i class="fas fa-wallet"></i> Secure Wallet</h5>
            <p class="card-text">Fund your wallet and pay for services with ease and security.</p>
        </div>
    </div>
    <!-- VTU Top Partners Section -->
    <div class="row mt-4 mb-5 justify-content-center">
        <div class="col-12 text-center mb-3">
            <h4 class="fw-bold" style="color:var(--primary);font-family:'Inter',Arial,sans-serif;">Our Top Partners</h4>
            <p class="text-muted" style="color:var(--text-light);">We work with Nigeria's most reliable networks and service providers</p>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="assets/images/partner-mtn.png" alt="MTN" class="img-fluid rounded shadow-sm bg-white p-2" style="max-height:60px;box-shadow:0 0 12px var(--primary);">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="assets/images/partner-glo.jpeg" alt="Glo" class="img-fluid rounded shadow-sm bg-white p-2" style="max-height:60px;box-shadow:0 0 12px var(--accent);">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="assets/images/partner-airtel.jpeg" alt="Airtel" class="img-fluid rounded shadow-sm bg-white p-2" style="max-height:60px;box-shadow:0 0 12px var(--primary-light);">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="assets/images/partner-9mobile.png" alt="9mobile" class="img-fluid rounded shadow-sm bg-white p-2" style="max-height:60px;box-shadow:0 0 12px var(--secondary);">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="assets/images/partner-eko.jpeg" alt="Eko Electric" class="img-fluid rounded shadow-sm bg-white p-2" style="max-height:60px;box-shadow:0 0 12px var(--accent);">
        </div>
        <div class="col-6 col-md-2 mb-3">
            <img src="assets/images/partner-dstv.jpeg" alt="DSTV" class="img-fluid rounded shadow-sm bg-white p-2" style="max-height:60px;box-shadow:0 0 12px var(--primary);">
        </div>
    </div>
    <!-- Arcade Promo Banner -->
    <div class="arcade-banner">
        <h2>🔥 Get 5% Bonus on Every Airtime Purchase!</h2>
        <p>Limited time offer. <a href="promo.php">Learn more</a></p>
    </div>
    <!-- How It Works Section -->
    <div class="dashboard-cards" style="margin-bottom:2.5rem;">
        <div class="card">
            <i class="fas fa-user-plus fa-2x" style="color:var(--primary);"></i>
            <h5 class="card-title mt-2">1. Register</h5>
            <p class="card-text">Create your free account in seconds.</p>
        </div>
        <div class="card">
            <i class="fas fa-wallet fa-2x" style="color:var(--secondary);"></i>
            <h5 class="card-title mt-2">2. Fund Wallet</h5>
            <p class="card-text">Add money to your wallet using multiple payment options.</p>
        </div>
        <div class="card">
            <i class="fas fa-bolt fa-2x" style="color:var(--accent);"></i>
            <h5 class="card-title mt-2">3. Buy & Pay</h5>
            <p class="card-text">Purchase airtime, data, and pay bills instantly.</p>
        </div>
    </div>
    <!-- Testimonials Section -->
    <div class="dashboard-cards" style="margin-bottom:2.5rem;">
        <div class="card">
            <i class="fas fa-quote-left fa-2x" style="color:var(--primary);"></i>
            <p class="card-text mt-3">“Super fast and reliable! I always get my airtime and data instantly.”</p>
            <h6 class="card-title mt-2" style="color:var(--primary);">- Chinedu, Lagos</h6>
        </div>
        <div class="card">
            <i class="fas fa-quote-left fa-2x" style="color:var(--secondary);"></i>
            <p class="card-text mt-3">“The bonuses and referral rewards are amazing. Highly recommend!”</p>
            <h6 class="card-title mt-2" style="color:var(--secondary);">- Aisha, Abuja</h6>
        </div>
        <div class="card">
            <i class="fas fa-quote-left fa-2x" style="color:var(--accent);"></i>
            <p class="card-text mt-3">“VTUNaija is my go-to for all bill payments. The wallet is super secure.”</p>
            <h6 class="card-title mt-2" style="color:var(--accent);">- Emeka, Port Harcourt</h6>
        </div>
    </div>
    <!-- Frequently Asked Questions Section -->
    <div class="dashboard-cards" style="margin-bottom:2.5rem;">
        <div class="card" style="padding:0;">
            <div class="accordion" id="faqAccordion" style="border:none;">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq1-heading">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                            How do I fund my wallet?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faq1-heading" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Log in to your account, go to the Wallet section, and choose your preferred payment method to fund your wallet instantly.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq2-heading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                            What services can I pay for?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faq2-heading" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can buy airtime, data bundles, pay for cable TV, electricity, and other bills on VTUNaija.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq3-heading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                            Is my wallet and transaction secure?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faq3-heading" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, your wallet and all transactions are protected with industry-standard security and encryption.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq4-heading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                            How do I contact support?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" aria-labelledby="faq4-heading" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can reach our support team via the Contact Us page, live chat, or email. We are available 24/7 to assist you.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<?php include 'includes/spinner.php'; ?>

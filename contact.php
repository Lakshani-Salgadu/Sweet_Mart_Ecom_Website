<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "Contact Us";
if ($_SERVER["REQUEST_METHOD"]==="POST") {
  $name  = trim($_POST["name"]    ?? "");
  $email = trim($_POST["email"]   ?? "");
  $subj  = trim($_POST["subject"] ?? "");
  $msg   = trim($_POST["message"] ?? "");
  if ($name && $email && $msg) {
    $stmt = $conn->prepare("INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss",$name,$email,$subj,$msg);
    $stmt->execute();
    setFlash("success","Thank you! We will get back to you within 24 hours. 😊");
  } else {
    setFlash("error","Please fill in all required fields.");
  }
  header("Location: contact.php"); exit;
}
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero text-center"><div class="container"><h1 class="section-title">Get in Touch</h1><p class="section-sub">We would love to hear from you!</p></div></div>
<section class="section-pad">
  <div class="container">
    <div class="row g-5">
      <!-- Form -->
      <div class="col-lg-7">
        <div class="cart-summary-box">
          <h4 style="margin-bottom:1.5rem;">Send Us a Message</h4>
          <form method="POST" class="checkout-form">
            <div class="row g-3">
              <div class="col-md-6 mb-3"><label class="form-label">Name *</label><input name="name" class="form-control" required id="contact-name"></div>
              <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input name="email" type="email" class="form-control" required id="contact-email"></div>
            </div>
            <div class="mb-3"><label class="form-label">Subject</label><input name="subject" class="form-control" id="contact-subject" placeholder="e.g. Custom Order Enquiry"></div>
            <div class="mb-4"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="5" required id="contact-message" placeholder="Tell us how we can help..."></textarea></div>
            <button type="submit" class="btn-primary-sm" id="send-msg-btn" style="padding:13px 32px;">Send Message ✉️</button>
          </form>
        </div>
      </div>
      <!-- Info -->
      <div class="col-lg-5">
        <div class="cart-summary-box mb-4">
          <h4 style="margin-bottom:1.2rem;">Contact Information</h4>
          <div class="footer-contact">
            <li><i class="bi bi-geo-alt" style="color:var(--pink);"></i><span>45/A, Galle Road, Colombo 06, Sri Lanka</span></li>
            <li><i class="bi bi-telephone" style="color:var(--pink);"></i><a href="tel:+94771234567" style="color:var(--text);">+94 77 123 4567</a></li>
            <li><i class="bi bi-envelope" style="color:var(--pink);"></i><a href="mailto:hello@sweetmart.lk" style="color:var(--text);">hello@sweetmart.lk</a></li>
            <li><i class="bi bi-clock" style="color:var(--pink);"></i><span>Mon–Sat: 8:00 AM – 8:00 PM</span></li>
          </div>
        </div>
        <div class="cart-summary-box">
          <h4 style="margin-bottom:1rem;">Follow Us</h4>
          <div class="d-flex gap-2 flex-wrap">
            <?php
            $socials=[["bi-facebook","Facebook","#"],["bi-instagram","Instagram","#"],["bi-whatsapp","WhatsApp","#"],["bi-tiktok","TikTok","#"]];
            foreach($socials as [$ico,$name,$url]):
            ?>
            <a href="<?= $url ?>" class="social-link" style="background:var(--pink-light);color:var(--brown);" title="<?= $name ?>"><i class="bi <?= $ico ?>"></i></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>

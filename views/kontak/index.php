<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$successMessage = null;
$whatsappRedirectUrl = null;
$errors = [];
$oldInput = [];

if (isset($_SESSION['contact_form_success'])) {
    $successMessage = $_SESSION['contact_form_success'];
    unset($_SESSION['contact_form_success']);
}
if (isset($_SESSION['contact_form_whatsapp_url'])) {
    $whatsappRedirectUrl = $_SESSION['contact_form_whatsapp_url'];
    unset($_SESSION['contact_form_whatsapp_url']);
}
if (isset($_SESSION['contact_form_errors'])) {
    $errors = $_SESSION['contact_form_errors'];
    unset($_SESSION['contact_form_errors']);
}
if (isset($_SESSION['contact_form_old_input'])) {
    $oldInput = $_SESSION['contact_form_old_input'];
    unset($_SESSION['contact_form_old_input']);
}

// 2. Tangani request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi
    $name = trim(htmlspecialchars($_POST['name'] ?? ''));
    $phone = trim(htmlspecialchars($_POST['telephone'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $purpose = trim(htmlspecialchars($_POST['purpose'] ?? ''));
    $message_content = trim(htmlspecialchars($_POST['message'] ?? ''));

    $current_errors = [];
    if (empty($name)) $current_errors['name'] = "Nama wajib diisi.";
    if (empty($phone)) $current_errors['telephone'] = "Telepon wajib diisi.";
    elseif (!preg_match('/^\+?[0-9\s-]{7,15}$/', $phone)) {
        $current_errors['telephone'] = "Format telepon tidak valid.";
    }
    if (empty($email)) $current_errors['email'] = "Email wajib diisi.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $current_errors['email'] = "Format email tidak valid";
    }
    if (empty($purpose)) $current_errors['purpose'] = "Tujuan wajib dipilih.";
    if (empty($message_content)) $current_errors['message'] = "Pesan wajib diisi.";


    if (empty($current_errors)) {
        // Jika berhasil (tidak ada error validasi)
        $waMessage = "Halo Mang Oman,\n\n";
        $waMessage .= "Saya ingin menghubungi Anda terkait :\n";
        $waMessage .= "Nama : " . $name . "\n";
        $waMessage .= "Telepon : " . $phone . "\n";
        $waMessage .= "Email : " . $email . "\n";
        $waMessage .= "Tujuan : " . $purpose . "\n";
        $waMessage .= "Pesan :\n" . $message_content . "\n\n";
        $waMessage .= "Terima kasih.";

        $targetWhatsAppNumber = '+6289630152631';
        $cleanWhatsAppNumber = preg_replace('/[^0-9]/', '', $targetWhatsAppNumber);
        if (substr($cleanWhatsAppNumber, 0, 2) !== '62' && substr($cleanWhatsAppNumber, 0, 1) === '0') {
            $cleanWhatsAppNumber = '62' . substr($cleanWhatsAppNumber, 1);
        } elseif (substr($cleanWhatsAppNumber, 0, 1) === '+') {
            $cleanWhatsAppNumber = substr($cleanWhatsAppNumber, 1);
        }

        $generatedWhatsAppUrl = "https://wa.me/" . $cleanWhatsAppNumber . "?text=" . rawurlencode($waMessage);

        $_SESSION['contact_form_success'] = "Diterima! Anda akan segera dialihkan ke WhatsApp.";
        $_SESSION['contact_form_whatsapp_url'] = $generatedWhatsAppUrl;

    } else {
        $_SESSION['contact_form_errors'] = $current_errors;
        $_SESSION['contact_form_old_input'] = $_POST;
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Include header
include __DIR__ . '/../../include/header.php';
?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <img src="http://localhost/PROJECR2/img/bakar.jpg" alt="..." class="hero-bg">
</header>

<div class="container my-5">
    <div class="row">
        <!-- Formulir Kontak -->
        <div class="col-md-6 mb-4">
            <h4>Formulir Kontak</h4>

            <?php
            if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                    </svg>
                    <div><?= htmlspecialchars($successMessage) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php
            if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Mohon perbaiki kesalahan berikut:</strong><br>
                    <ul>
                        <?php foreach ($errors as $field => $errorMsg): ?>
                            <li><?= htmlspecialchars($errorMsg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama *</label>
                    <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="telephone" class="form-label">Telepon *</label>
                    <input type="tel" class="form-control <?= isset($errors['telephone']) ? 'is-invalid' : '' ?>" id="telephone" name="telephone" value="<?= htmlspecialchars($oldInput['telephone'] ?? '') ?>" required>
                    <?php if (isset($errors['telephone'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['telephone']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3 contact-options">
                    <label class="form-label">Pilih salah satu sesuai tujuan anda: *</label>
                    <?php $selectedPurpose = $oldInput['purpose'] ?? ''; ?>
                    <div class="form-check">
                        <input class="form-check-input <?= isset($errors['purpose']) ? 'is-invalid' : '' ?>" type="radio" name="purpose" id="catering" value="Catering" <?= ($selectedPurpose == 'Catering') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="catering">Catering</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input <?= isset($errors['purpose']) ? 'is-invalid' : '' ?>" type="radio" name="purpose" id="kerjasama" value="Kerjasama" <?= ($selectedPurpose == 'Kerjasama') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="kerjasama">Kerjasama/Partnership</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input <?= isset($errors['purpose']) ? 'is-invalid' : '' ?>" type="radio" name="purpose" id="lainnya" value="Lainnya" <?= ($selectedPurpose == 'Lainnya') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="lainnya">Lainnya</label>
                        <?php if (isset($errors['purpose'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($errors['purpose']) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Isi Pesan *</label>
                    <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" id="message" name="message" rows="4" required><?= htmlspecialchars($oldInput['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['message']) ?></div><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

        <!-- Info Kantor -->
        <div class="col-md-6 mb-4">
            <div class="info-kantor">
                <h5><strong>KANTOR</strong></h5>
                <p><i class="fas fa-map-marker-alt"></i> Jln. Jendral Sudirman No. 45 RT 03 RW 07</p>
                <p><i class="fab fa-whatsapp"></i> 089630152631 (Mang Oman)</p>
                <p><i class="fas fa-envelope"></i> info@warungmangoman.com</p>
                <div class="service-hours mt-3">
                    <p><strong>Service Hours :</strong></p>
                    <p>Senin - Sabtu</p>
                    <p>Fast Response Chat 10.00 - 22.00</p>
                    <p>Pengiriman Di Sesuaikan dengan<br>Permintaan Costumer</p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../../include/footer.php'; ?>

<?php
if (isset($whatsappRedirectUrl) && !empty($whatsappRedirectUrl)):
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Mempersiapkan redirect ke WhatsApp...");
        setTimeout(function() {
            console.log("Redirecting to: <?= addslashes($whatsappRedirectUrl) ?>");
            window.location.href = '<?= addslashes($whatsappRedirectUrl) ?>';
        }, 2500);
    });
</script>
<?php
endif;

include __DIR__ . '/../../include/script.php';
?>
<?php include __DIR__ . '/../../include/header.php'; ?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <img src="http://localhost/PROJECR2/img/bakar.jpg" alt="..." class="hero-bg">   
</header>

<div class="container my-5">
    <div class="row">
        <!-- Formulir Kontak -->
        <div class="col-md-6 mb-4">
            <h4>Formulir Kontak</h4>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">Pesan Anda telah dikirim!</div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telepon *</label>
                    <input type="tel" class="form-control" name="telephone" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3 contact-options">
                    <label class="form-label">Pilih salah satu sesuai tujuan anda</label>
                    <label><input type="radio" name="purpose" value="Catering"> Catering</label>
                    <label><input type="radio" name="purpose" value="Kerjasama"> Kerjasama/Partnership</label>
                    <label><input type="radio" name="purpose" value="Lainnya"> Lainnya</label>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Isi Pesan *</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

        <!-- Info Kantor -->
        <div class="col-md-6 mb-4">
            <div class="info-kantor">
                <h5><strong>KANTOR</strong></h5>
                <p><i class="fas fa-map-marker-alt"></i> Jln. Jendral Sudirman No. 45 RT 03 RW 07</p>
                <p><i class="fab fa-whatsapp"></i> 089630152631 (Ahmad Fauzil Adhim)</p>
                <p><i class="fas fa-envelope"></i> info@miemanyala.com</p>
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
<?php include __DIR__ . '/../../include/script.php'; ?>

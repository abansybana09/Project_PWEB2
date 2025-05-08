<?php
// File: /Admin/Order.php

// Pastikan session dimulai (jika belum di file utama yang include ini)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include Koneksi.php (pastikan path benar)
require_once "Koneksi.php";

// Load variabel environment dari .env
// Path ke folder yang berisi .env (root proyek)
require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer Autoloader
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Path ke folder PROJECR2
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // Handle error jika .env tidak ditemukan (opsional)
    // die("Error: File .env tidak ditemukan. Pastikan file .env ada di root proyek.");
    // Atau set default jika tidak ada .env (tidak disarankan untuk production)
     error_log("Warning: File .env tidak ditemukan, menggunakan nilai default (jika ada).");
}


// Ambil Client Key dari environment variable untuk JavaScript
// Gunakan nilai default jika getenv tidak mengembalikan apa-apa
$midtransClientKey = getenv('MIDTRANS_CLIENT_KEY') ?: 'CLIENT_KEY_ANDA_JIKA_ENV_GAGAL';
$isProduction = (getenv('MIDTRANS_IS_PRODUCTION') === 'true'); // Cek environment

// Ambil data order
$query = mysqli_query($conn, "SELECT * FROM tb_order ORDER BY id DESC"); // Urutkan dari terbaru mungkin lebih baik
$result = [];
$query_error = null;

if ($query) {
    while ($record = mysqli_fetch_array($query)) {
        $result[] = $record;
    }
} else {
    $query_error = "Error mengambil data order: " . mysqli_error($conn);
}
?>

<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            Data Orderan
        </div>
        <div class="card-body">
            <?php
            // Tampilkan pesan status dari session (setelah redirect dari proses)
            if (isset($_SESSION['status_message'])) {
                $message = $_SESSION['status_message'];
                echo '<div class="alert alert-' . htmlspecialchars($message['type']) . ' alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($message['text']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['status_message']);
            }

            // Tampilkan error query jika ada
            if (isset($query_error) && !empty($query_error)) {
                echo '<div class="alert alert-danger" role="alert">';
                echo htmlspecialchars($query_error);
                echo '</div>';
            }
            ?>

            <?php if (empty($result) && !$query_error) : ?>
                <p>Data Order Tidak Ada.</p>
            <?php elseif (!empty($result)) : ?>
                <div class="table-responsive">
                    <div class="d-flex justify-content-end">
                        <div class="mb-3" style="width: 200px;">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari Pelanggan">
                        </div>
                    </div>
                    <table class="table table-hover" id="orderTable">
                        <thead>
                            <tr class="text-nowrap">
                                <th scope="col">No</th>
                                <th scope="col">Pelanggan</th>
                                <th scope="col">No HP</th>
                                <th scope="col">Alamat</th>
                                <th scope="col">Pesanan</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Total Harga</th>
                                <th scope="col">Pembayaran</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $display_no = 1; // Nomor urut tampilan
                            foreach ($result as $row) :
                                // Tentukan apakah tombol bayar harus ditampilkan
                                $isPaid = (strtolower($row['pembayaran']) === 'ya' || strtolower($row['pembayaran']) === 'success' || strtolower($row['pembayaran']) === 'settlement');
                            ?>
                                <tr>
                                    <th scope="row"><?= $display_no++ ?></th>
                                    <td><?= htmlspecialchars($row['pelanggan']) ?></td>
                                    <td><?= htmlspecialchars($row['nohp']) ?></td>
                                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['pesanan'])) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah_pesan']) ?></td>
                                    <td><?= 'Rp ' . number_format((float)preg_replace('/[^0-9.]/', '', $row['total_harga']), 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge <?= $isPaid ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= htmlspecialchars($row['pembayaran']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap"> <!-- flex-wrap agar tombol turun jika tidak muat -->
                                            <button class="btn btn-info btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#ViewData<?= $row['id'] ?>" title="Lihat Detail"><i class="bi bi-eye"></i></button>
                                            <button class="btn btn-warning btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#EditData<?= $row['id'] ?>" title="Edit Order"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-danger btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#HapusData<?= $row['id'] ?>" title="Hapus Order"><i class="bi bi-trash3"></i></button>
                                            <!-- Tombol Bayar Midtrans (hanya jika belum lunas) -->
                                            <?php if (!$isPaid) : ?>
                                                <button class="btn btn-primary btn-sm mb-1" onclick="initiateAdminSnapPayment(<?= $row['id'] ?>)" title="Bayar via Midtrans">
                                                    <i class="bi bi-credit-card"></i> Bayar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal View Data -->
                                <div class="modal fade" id="ViewData<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <!-- ... (Kode Modal View Anda) ... -->
                                     <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Order ID: <?= $row['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6"><p><strong>Pelanggan:</strong> <?= htmlspecialchars($row['pelanggan']) ?></p></div>
                                                    <div class="col-md-6"><p><strong>No HP:</strong> <?= htmlspecialchars($row['nohp']) ?></p></div>
                                                </div>
                                                <p><strong>Alamat:</strong> <?= htmlspecialchars($row['alamat']) ?></p>
                                                <p><strong>Pesanan:</strong><br><?= nl2br(htmlspecialchars($row['pesanan'])) ?></p>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-md-4"><p><strong>Jumlah:</strong> <?= htmlspecialchars($row['jumlah_pesan']) ?></p></div>
                                                    <div class="col-md-4"><p><strong>Total Harga:</strong> <?= 'Rp ' . number_format((float)preg_replace('/[^0-9.]/', '', $row['total_harga']), 0, ',', '.') ?></p></div>
                                                    <div class="col-md-4"><p><strong>Status Pembayaran:</strong> <?= htmlspecialchars($row['pembayaran']) ?></p></div>
                                                </div>
                                                <p><small>Midtrans Order ID: <?= htmlspecialchars($row['midtrans_order_id'] ?? '-') ?></small></p>
                                                <p><small>Midtrans Transaction ID: <?= htmlspecialchars($row['midtrans_transaction_id'] ?? '-') ?></small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Edit Data -->
                                <div class="modal fade" id="EditData<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <!-- ... (Kode Modal Edit Anda) ... -->
                                     <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Order ID: <?= $row['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="../Proses/Edit_order.php" method="POST" class="needs-validation" novalidate>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-floating mb-3">
                                                                <input type="text" class="form-control" name="pelanggan" required value="<?= htmlspecialchars($row['pelanggan']) ?>">
                                                                <label>Nama Pelanggan</label>
                                                                <div class="invalid-feedback">Nama pelanggan wajib diisi.</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-floating mb-3">
                                                                <input type="text" class="form-control" name="nohp" required value="<?= htmlspecialchars($row['nohp']) ?>">
                                                                <label>No HP</label>
                                                                <div class="invalid-feedback">No HP wajib diisi.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-floating mb-3">
                                                                <input type="text" class="form-control" name="alamat" required value="<?= htmlspecialchars($row['alamat']) ?>">
                                                                <label>Alamat</label>
                                                                <div class="invalid-feedback">Alamat wajib diisi.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-floating mb-3">
                                                                <textarea class="form-control" style="height: 100px" name="pesanan" required><?= htmlspecialchars($row['pesanan']) ?></textarea>
                                                                <label>Pesanan</label>
                                                                <div class="invalid-feedback">Pesanan wajib diisi.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-floating mb-3">
                                                                <input type="number" class="form-control" name="jumlah_pesan" required value="<?= htmlspecialchars($row['jumlah_pesan']) ?>" min="1">
                                                                <label>Jumlah Pesanan</label>
                                                                <div class="invalid-feedback">Jumlah pesanan wajib diisi (angka).</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating mb-3">
                                                                <!-- Pastikan format harga sesuai saat disimpan -->
                                                                <input type="number" step="any" class="form-control" name="total_harga" required value="<?= preg_replace('/[^0-9.]/', '', $row['total_harga']) ?>">
                                                                <label>Total Harga (Angka)</label>
                                                                <div class="invalid-feedback">Total harga wajib diisi (angka).</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating mb-3">
                                                                <select class="form-select" name="pembayaran" required>
                                                                    <option value="" disabled>Pilih Status</option>
                                                                    <option value="Ya" <?= (strtolower($row['pembayaran']) == 'ya') ? 'selected' : '' ?>>Ya</option>
                                                                    <option value="Tidak" <?= (strtolower($row['pembayaran']) == 'tidak') ? 'selected' : '' ?>>Tidak</option>
                                                                    <option value="Pending" <?= (strtolower($row['pembayaran']) == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                                    <option value="Gagal" <?= (strtolower($row['pembayaran']) == 'gagal') ? 'selected' : '' ?>>Gagal</option>
                                                                    <option value="Kadaluarsa" <?= (strtolower($row['pembayaran']) == 'kadaluarsa') ? 'selected' : '' ?>>Kadaluarsa</option>
                                                                    <option value="Dibatalkan" <?= (strtolower($row['pembayaran']) == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                                                                    <option value="Challenge" <?= (strtolower($row['pembayaran']) == 'challenge') ? 'selected' : '' ?>>Challenge</option>
                                                                </select>
                                                                <label>Pembayaran</label>
                                                                <div class="invalid-feedback">Status pembayaran wajib dipilih.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary" name="submit_edit_order">Save</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus Data -->
                                <div class="modal fade" id="HapusData<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <!-- ... (Kode Modal Hapus Anda) ... -->
                                     <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5">Hapus Order ID: <?= $row['id'] ?></h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus order dari <b><?= htmlspecialchars($row['pelanggan']) ?></b>?</p>
                                                <form class="needs-validation" novalidate action="../Proses/Delete_order.php" method="POST">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger" name="submit_order_validate" value="123">Delete</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div> <!-- Akhir div.card-body -->
    </div> <!-- Akhir div.card -->

    <!-- Div untuk pesan status pembayaran Snap (opsional) -->
    <div id="adminSnapMessage" class="mt-3"></div>

</div> <!-- Akhir div.col-lg-9 -->

<!-- =========================================================== -->
<!-- SCRIPT MIDTRANS SNAP.JS (DARI CDN)                         -->
<!-- =========================================================== -->
<!-- Pilih URL sesuai environment (Sandbox/Production) -->
<script type="text/javascript"
    src="<?= $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>"
    data-client-key="<?= htmlspecialchars($midtransClientKey) ?>"></script>
<!-- =========================================================== -->

<script>
    // Fungsi untuk validasi form Bootstrap
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })();

    // Script Search
    document.addEventListener('DOMContentLoaded', (event) => {
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("keyup", function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll("#orderTable tbody tr");

                rows.forEach(row => {
                    const pelangganCell = row.cells[1]; // Kolom kedua (index 1) adalah Pelanggan
                    if (pelangganCell) {
                        const pelanggan = pelangganCell.textContent.toLowerCase();
                        row.style.display = pelanggan.includes(filter) ? "" : "none";
                    }
                });
            });
        }
    });

    // ===========================================================
    // FUNGSI UNTUK MEMULAI PEMBAYARAN SNAP DARI ADMIN
    // ===========================================================
    async function initiateAdminSnapPayment(orderId) {
        const snapMessageDiv = document.getElementById('adminSnapMessage');
        snapMessageDiv.innerHTML = '<div class="alert alert-info">Meminta token pembayaran...</div>';

        // Disable tombol yang diklik (opsional)
        const clickedButton = event.target.closest('button');
        if(clickedButton) clickedButton.disabled = true;

        try {
            // Panggil backend untuk mendapatkan Snap Token berdasarkan ID Order Internal
            // Buat file PHP baru ini: ../Proses/CreateSnapTokenForOrder.php
            const response = await fetch(`../Proses/CreateSnapToken.php?order_id=${orderId}`, {
                method: 'GET' // Atau POST jika Anda lebih suka
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Gagal mendapatkan token: ${response.status}. ${errorText}`);
            }

            const result = await response.json();

            if (result.success && result.snap_token) {
                snapMessageDiv.innerHTML = ''; // Hapus pesan loading

                // Panggil Snap Popup
                snap.pay(result.snap_token, {
                    onSuccess: function(paymentResult){
                        console.log('Admin Payment Success:', paymentResult);
                        snapMessageDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">Pembayaran untuk Order ID ${paymentResult.order_id} berhasil! Status: ${paymentResult.transaction_status}. Halaman akan dimuat ulang.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                        // Muat ulang halaman setelah beberapa detik untuk melihat status terupdate
                        setTimeout(() => { window.location.reload(); }, 4000);
                    },
                    onPending: function(paymentResult){
                        console.log('Admin Payment Pending:', paymentResult);
                        snapMessageDiv.innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">Pembayaran untuk Order ID ${paymentResult.order_id} tertunda. Status: ${paymentResult.transaction_status}.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                        if(clickedButton) clickedButton.disabled = false; // Enable lagi tombolnya
                    },
                    onError: function(paymentResult){
                        console.log('Admin Payment Error:', paymentResult);
                        snapMessageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">Pembayaran untuk Order ID ${paymentResult.order_id} gagal. Pesan: ${paymentResult.status_message}.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                        if(clickedButton) clickedButton.disabled = false; // Enable lagi tombolnya
                    },
                    onClose: function(){
                        console.log('Admin Snap popup closed.');
                        // Hanya tampilkan pesan jika belum ada pesan sukses/pending/error
                         if (!snapMessageDiv.querySelector('.alert-success') && !snapMessageDiv.querySelector('.alert-warning') && !snapMessageDiv.querySelector('.alert-danger')) {
                             snapMessageDiv.innerHTML = '<div class="alert alert-secondary alert-dismissible fade show" role="alert">Popup pembayaran ditutup.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                         }
                        if(clickedButton) clickedButton.disabled = false; // Enable lagi tombolnya
                    }
                });

            } else {
                throw new Error(result.message || 'Token Snap tidak diterima dari server.');
            }

        } catch (error) {
            console.error('Error saat initiateAdminSnapPayment:', error);
            snapMessageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">Terjadi kesalahan: ${error.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            if(clickedButton) clickedButton.disabled = false; // Enable lagi tombolnya
        }
    }
</script>
<?php
// File: /Admin/Order.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hapus atau komentari var_dump GET jika filter sudah berfungsi
// echo "<pre style='background: #f2f2f2; padding: 15px; border: 1px solid #ccc; margin-bottom: 20px; font-family: monospace;'>";
// echo "<strong>DEBUG: Parameter GET yang diterima PHP saat load halaman:</strong>\n";
// var_dump($_GET);
// echo "</pre>";

require_once "Koneksi.php"; // Koneksi Database
require_once __DIR__ . '/../vendor/autoload.php'; // Composer
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Path ke folder PROJECR2
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    error_log("Admin/Order.php Warning: File .env tidak ditemukan.");
}

$midtransClientKey = $_ENV['MIDTRANS_CLIENT_KEY'] ?? $_SERVER['MIDTRANS_CLIENT_KEY'] ?? 'CLIENT_KEY_ANDA_JIKA_ENV_GAGAL';
$isProduction = (strtolower($_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true');

// --- Inisialisasi Variabel Filter ---
$filter_tanggal = !empty($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : date('Y-m-d');
$filter_bulan = !empty($_GET['filter_bulan']) ? $_GET['filter_bulan'] : date('Y-m');
$filter_tahun = !empty($_GET['filter_tahun']) ? $_GET['filter_tahun'] : date('Y');

error_log("Admin/Order.php - Filter DIGUNAKAN: Tanggal=" . $filter_tanggal . ", Bulan=" . $filter_bulan . ", Tahun=" . $filter_tahun);

$daily_omset = 0; $monthly_omset = 0; $yearly_omset = 0;
$orders_for_table = [];
$query_error_messages = []; $conn_error = null;

if (!$conn || $conn->connect_error) { $conn_error = "Koneksi DB gagal: " . ($conn->connect_error ?? 'Tidak diketahui'); error_log($conn_error); }
else {
    // Fungsi helper untuk membersihkan dan cast harga
    function getCleanPrice($price_string) {
        return (float)preg_replace('/[^0-9.]/', '', str_replace('Rp', '', $price_string));
    }

    // --- Query untuk Omset Harian ---
    $sql_daily_omset = "SELECT SUM(CAST(REPLACE(REPLACE(total_harga, 'Rp', ''), '.', '') AS DECIMAL(15,2))) AS total_omset_harian FROM tb_order WHERE DATE(waktu_order_dibuat) = ? AND LOWER(pembayaran) IN ('ya', 'success', 'settlement')";
    $stmt_daily = $conn->prepare($sql_daily_omset);
    if ($stmt_daily) { $stmt_daily->bind_param("s", $filter_tanggal); if ($stmt_daily->execute()) { $result_daily = $stmt_daily->get_result(); if ($row_daily = $result_daily->fetch_assoc()) { $daily_omset = (float)($row_daily['total_omset_harian'] ?? 0); } } else { $query_error_messages[] = "Exec daily: " . $stmt_daily->error; } $stmt_daily->close(); } else { $query_error_messages[] = "Prep daily: " . $conn->error; }

    // --- Query untuk Omset Bulanan ---
    $sql_monthly_omset = "SELECT SUM(CAST(REPLACE(REPLACE(total_harga, 'Rp', ''), '.', '') AS DECIMAL(15,2))) AS total_omset_bulanan FROM tb_order WHERE DATE_FORMAT(waktu_order_dibuat, '%Y-%m') = ? AND LOWER(pembayaran) IN ('ya', 'success', 'settlement')";
    $stmt_monthly = $conn->prepare($sql_monthly_omset);
    if ($stmt_monthly) { $stmt_monthly->bind_param("s", $filter_bulan); if ($stmt_monthly->execute()) { $result_monthly = $stmt_monthly->get_result(); if ($row_monthly = $result_monthly->fetch_assoc()) { $monthly_omset = (float)($row_monthly['total_omset_bulanan'] ?? 0); } } else { $query_error_messages[] = "Exec monthly: " . $stmt_monthly->error; } $stmt_monthly->close(); } else { $query_error_messages[] = "Prep monthly: " . $conn->error; }

    // --- Query untuk Omset Tahunan ---
    $sql_yearly_omset = "SELECT SUM(CAST(REPLACE(REPLACE(total_harga, 'Rp', ''), '.', '') AS DECIMAL(15,2))) AS total_omset_tahunan FROM tb_order WHERE DATE_FORMAT(waktu_order_dibuat, '%Y') = ? AND LOWER(pembayaran) IN ('ya', 'success', 'settlement')";
    $stmt_yearly = $conn->prepare($sql_yearly_omset);
    if ($stmt_yearly) { $stmt_yearly->bind_param("s", $filter_tahun); if ($stmt_yearly->execute()) { $result_yearly = $stmt_yearly->get_result(); if ($row_yearly = $result_yearly->fetch_assoc()) { $yearly_omset = (float)($row_yearly['total_omset_tahunan'] ?? 0); } } else { $query_error_messages[] = "Exec yearly: " . $stmt_yearly->error; } $stmt_yearly->close(); } else { $query_error_messages[] = "Prep yearly: " . $conn->error; }

    // --- Query untuk tabel order (berdasarkan $filter_tanggal) ---
    $sql_orders_table = "SELECT * FROM tb_order WHERE DATE(waktu_order_dibuat) = ? ORDER BY id DESC";
    $stmt_orders_table = $conn->prepare($sql_orders_table);
    if ($stmt_orders_table) {
        $stmt_orders_table->bind_param("s", $filter_tanggal);
        if ($stmt_orders_table->execute()) {
            $query_result_table = $stmt_orders_table->get_result();
            while ($record = $query_result_table->fetch_assoc()) {
                $orders_for_table[] = $record;
            }
        } else { $query_error_messages[] = "Execute orders table: " . $stmt_orders_table->error; }
        $stmt_orders_table->close();
    } else { $query_error_messages[] = "Prepare orders table: " . $conn->error; }
}

if (!empty($query_error_messages)) { error_log("Admin/Order.php SQL Errors: " . implode(" | ", $query_error_messages)); }
?>

<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">Data Orderan</div>
        <div class="card-body">
            <?php
            if (isset($_SESSION['status_message'])) {
                $message = $_SESSION['status_message'];
                echo '<div class="alert alert-' . htmlspecialchars($message['type']) . ' alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($message['text']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['status_message']);
            }
            if ($conn_error) { echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($conn_error) . '</div>'; }
            if (!empty($query_error_messages) && !$conn_error) { echo '<div class="alert alert-warning" role="alert">Masalah query: ' . htmlspecialchars(implode("; ", $query_error_messages)) . ' (Periksa log server untuk detail)</div>';}
            ?>

            <!-- Filter Form -->
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <?php
                if (isset($_GET['x'])) { echo '<input type="hidden" name="x" value="' . htmlspecialchars($_GET['x']) . '">'; }
                elseif (isset($_GET['page'])) { echo '<input type="hidden" name="page" value="' . htmlspecialchars($_GET['page']) . '">'; }
                // else { echo '<input type="hidden" name="x" value="Order">'; } // Jika perlu default routing
                ?>
                <div class="row align-items-end mb-3">
                    <div class="col-md-3 mb-2">
                        <label for="filter_tanggal_input" class="form-label">Tgl. Order & Omset Harian:</label>
                        <input type="date" class="form-control form-control-sm" id="filter_tanggal_input" name="filter_tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="filter_bulan_input" class="form-label">Bulan Omset:</label>
                        <input type="month" class="form-control form-control-sm" id="filter_bulan_input" name="filter_bulan" value="<?= htmlspecialchars($filter_bulan) ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="filter_tahun_input" class="form-label">Tahun Omset:</label>
                        <input type="number" class="form-control form-control-sm" id="filter_tahun_input" name="filter_tahun" value="<?= htmlspecialchars($filter_tahun) ?>" min="2020" max="<?= date('Y') ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-info btn-sm w-100">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <!-- Tampilan Omset -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Omset <?= date('d M Y', strtotime($filter_tanggal)) ?></h6>
                                    <p class="card-text fs-5 fw-bold mb-0">Rp <?= number_format($daily_omset, 0, ',', '.') ?></p>
                                </div>
                                <a href="../Proses/DownloadNotaOmsetHarian.php?tanggal=<?= htmlspecialchars($filter_tanggal) ?>" class="btn btn-light btn-sm" title="Download Nota Harian" target="_blank">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                             <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Omset <?= date('M Y', strtotime($filter_bulan . '-01')) ?></h6>
                                    <p class="card-text fs-5 fw-bold mb-0">Rp <?= number_format($monthly_omset, 0, ',', '.') ?></p>
                                </div>
                                <a href="../Proses/DownloadNotaOmsetBulanan.php?bulan=<?= htmlspecialchars($filter_bulan) ?>" class="btn btn-light btn-sm" title="Download Nota Bulanan" target="_blank">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">Omset <?= $filter_tahun ?></h6>
                                    <p class="card-text fs-5 fw-bold mb-0">Rp <?= number_format($yearly_omset, 0, ',', '.') ?></p>
                                </div>
                                <a href="../Proses/DownloadNotaOmsetTahunan.php?tahun=<?= htmlspecialchars($filter_tahun) ?>" class="btn btn-light btn-sm" title="Download Nota Tahunan" target="_blank">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penampilan Tabel Order atau Pesan "Tidak Ada Order" -->
            <?php
            if (!$conn_error && empty(array_filter($query_error_messages, function($msg){ return strpos(strtolower($msg), 'orders table') !== false; })) ) :
                if (!empty($orders_for_table)) :
            ?>
                    <div class="table-responsive">
                        <div class="d-flex justify-content-end"><div class="mb-3" style="width: 200px;"><input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari Pelanggan"></div></div>
                        <h5 class="mt-3">Detail Order Tanggal: <?= htmlspecialchars(date('d M Y', strtotime($filter_tanggal))) ?></h5>
                        <table class="table table-hover table-sm" id="orderTable">
                            <thead>
                                <tr class="text-nowrap">
                                    <th scope="col">No</th> <th scope="col">ID</th> <th scope="col">Pelanggan</th> <th scope="col">No HP</th>
                                    <th scope="col" style="min-width: 200px;">Pesanan</th> <th scope="col">Jml</th> <th scope="col">Total</th>
                                    <th scope="col">Bayar</th> <th scope="col">Waktu</th> <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $display_no_table = 1;
                                foreach ($orders_for_table as $row) :
                                    $isPaid = (in_array(strtolower($row['pembayaran']), ['ya', 'success', 'settlement']));
                                ?>
                                    <tr>
                                        <th scope="row"><?= $display_no_table++ ?></th>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['pelanggan']) ?></td>
                                        <td><?= htmlspecialchars($row['nohp']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['pesanan'])) ?></td>
                                        <td><?= htmlspecialchars($row['jumlah_pesan']) ?></td>
                                        <td><?= 'Rp ' . number_format((float)preg_replace('/[^0-9.]/', '', $row['total_harga']), 0, ',', '.') ?></td>
                                        <td><span class="badge <?= $isPaid ? 'bg-success' : (in_array(strtolower($row['pembayaran']), ['pending', 'tidak']) ? 'bg-warning text-dark' : 'bg-danger') ?>"><?= htmlspecialchars($row['pembayaran']) ?></span></td>
                                        <td><?= htmlspecialchars(date('d M Y H:i', strtotime($row['waktu_order_dibuat']))) ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap">
                                                <button class="btn btn-info btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#ViewData<?= $row['id'] ?>" title="Lihat Detail"><i class="bi bi-eye"></i></button>
                                                <button class="btn btn-warning btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#EditData<?= $row['id'] ?>" title="Edit Order"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-danger btn-sm me-1 mb-1" data-bs-toggle="modal" data-bs-target="#HapusData<?= $row['id'] ?>" title="Hapus Order"><i class="bi bi-trash3"></i></button>
                                                <?php if (!$isPaid && !empty($row['id'])) : ?>
                                                    <button class="btn btn-primary btn-sm mb-1" onclick="initiateAdminSnapPayment(<?= $row['id'] ?>)" title="Bayar via Midtrans"><i class="bi bi-credit-card"></i> Bayar</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal View Data -->
                                    <div class="modal fade" id="ViewData<?= $row['id'] ?>" tabindex="-1" aria-labelledby="ViewDataLabel<?= $row['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="ViewDataLabel<?= $row['id'] ?>">Detail Order ID: <?= htmlspecialchars($row['id']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
                                            <p><strong>Pelanggan:</strong> <?= htmlspecialchars($row['pelanggan']) ?></p><p><strong>No HP:</strong> <?= htmlspecialchars($row['nohp']) ?></p><p><strong>Alamat:</strong> <?= htmlspecialchars($row['alamat']) ?></p><p><strong>Pesanan:</strong><br><div style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($row['pesanan']) ?></div></p><hr>
                                            <p><strong>Jumlah:</strong> <?= htmlspecialchars($row['jumlah_pesan']) ?></p><p><strong>Total Harga:</strong> <?= 'Rp ' . number_format((float)preg_replace('/[^0-9.]/', '', $row['total_harga']), 0, ',', '.') ?></p><p><strong>Status Pembayaran:</strong> <?= htmlspecialchars($row['pembayaran']) ?></p>
                                            <p><small>Waktu Order: <?= htmlspecialchars(date('d M Y H:i:s', strtotime($row['waktu_order_dibuat']))) ?></small></p><p><small>Midtrans Order ID: <?= htmlspecialchars($row['midtrans_order_id'] ?? '-') ?></small></p><p><small>Midtrans Transaction ID: <?= htmlspecialchars($row['midtrans_transaction_id'] ?? '-') ?></small></p>
                                        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div></div></div>
                                    </div>

                                    <!-- Modal Edit Data -->
                                    <div class="modal fade" id="EditData<?= $row['id'] ?>" tabindex="-1" aria-labelledby="EditDataLabel<?= $row['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="EditDataLabel<?= $row['id'] ?>">Edit Order ID: <?= htmlspecialchars($row['id']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                                        <form action="../Proses/Edit_order.php" method="POST" class="needs-validation" novalidate><div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <div class="row"><div class="col-md-6"><div class="form-floating mb-3"><input type="text" class="form-control" name="pelanggan" required value="<?= htmlspecialchars($row['pelanggan']) ?>"><label>Nama Pelanggan</label><div class="invalid-feedback">Wajib diisi.</div></div></div><div class="col-md-6"><div class="form-floating mb-3"><input type="text" class="form-control" name="nohp" required value="<?= htmlspecialchars($row['nohp']) ?>"><label>No HP</label><div class="invalid-feedback">Wajib diisi.</div></div></div></div>
                                            <div class="row"><div class="col-12"><div class="form-floating mb-3"><input type="text" class="form-control" name="alamat" required value="<?= htmlspecialchars($row['alamat']) ?>"><label>Alamat</label><div class="invalid-feedback">Wajib diisi.</div></div></div></div>
                                            <div class="row"><div class="col-12"><div class="form-floating mb-3"><textarea class="form-control" style="height: 100px" name="pesanan" required><?= htmlspecialchars($row['pesanan']) ?></textarea><label>Pesanan</label><div class="invalid-feedback">Wajib diisi.</div></div></div></div>
                                            <div class="row"><div class="col-md-4"><div class="form-floating mb-3"><input type="number" class="form-control" name="jumlah_pesan" required value="<?= htmlspecialchars($row['jumlah_pesan']) ?>" min="1"><label>Jumlah Pesanan</label><div class="invalid-feedback">Wajib (angka).</div></div></div><div class="col-md-4"><div class="form-floating mb-3"><input type="number" step="any" class="form-control" name="total_harga" required value="<?= preg_replace('/[^0-9.]/', '', $row['total_harga']) ?>"><label>Total Harga (Angka)</label><div class="invalid-feedback">Wajib (angka).</div></div></div><div class="col-md-4"><div class="form-floating mb-3"><select class="form-select" name="pembayaran" required><option value="" disabled>Pilih Status</option><option value="Ya" <?= (strtolower($row['pembayaran']) == 'ya') ? 'selected' : '' ?>>Ya</option><option value="Tidak" <?= (strtolower($row['pembayaran']) == 'tidak') ? 'selected' : '' ?>>Tidak</option><option value="Pending" <?= (strtolower($row['pembayaran']) == 'pending') ? 'selected' : '' ?>>Pending</option><option value="Gagal" <?= (strtolower($row['pembayaran']) == 'gagal') ? 'selected' : '' ?>>Gagal</option><option value="Kadaluarsa" <?= (strtolower($row['pembayaran']) == 'kadaluarsa') ? 'selected' : '' ?>>Kadaluarsa</option><option value="Dibatalkan" <?= (strtolower($row['pembayaran']) == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option><option value="Challenge" <?= (strtolower($row['pembayaran']) == 'challenge') ? 'selected' : '' ?>>Challenge</option></select><label>Pembayaran</label><div class="invalid-feedback">Wajib dipilih.</div></div></div></div>
                                        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" name="submit_edit_order">Save</button></div></form>
                                        </div></div>
                                    </div>

                                    <!-- Modal Hapus Data -->
                                    <div class="modal fade" id="HapusData<?= $row['id'] ?>" tabindex="-1" aria-labelledby="HapusDataLabel<?= $row['id'] ?>" aria-hidden="true">
                                         <div class="modal-dialog modal-md"><div class="modal-content"><div class="modal-header"><h1 class="modal-title fs-5" id="HapusDataLabel<?= $row['id'] ?>">Hapus Order ID: <?= htmlspecialchars($row['id']) ?></h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
                                            <p>Apakah Anda yakin ingin menghapus order dari <b><?= htmlspecialchars($row['pelanggan']) ?></b>?</p>
                                            <form action="../Proses/Delete_order.php" method="POST"><input type="hidden" name="id" value="<?= $row['id'] ?>"><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger" name="submit_order_validate">Delete</button></div></form>
                                         </div></div></div>
                                    </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php
                elseif (empty($orders_for_table) && empty($query_error_messages) && !$conn_error) :
            ?>
                <div class="alert alert-info mt-3">
                    Tidak ada order untuk tanggal <?= htmlspecialchars(date('d M Y', strtotime($filter_tanggal))) ?>.
                </div>
            <?php
                endif;
            endif;
            ?>
        </div>
    </div>
    <div id="adminSnapMessage" class="mt-3"></div>
</div>

<!-- Script Midtrans Snap.js -->
<script type="text/javascript"
    src="<?= $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>"
    data-client-key="<?= htmlspecialchars($midtransClientKey) ?>"></script>

<script>
    // Fungsi validasi Bootstrap
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

    document.addEventListener('DOMContentLoaded', (event) => {
        console.log("Admin Order DOMContentLoaded: Initializing scripts...");
        const searchInput = document.getElementById("searchInput");
        const orderTable = document.getElementById("orderTable");
        if (searchInput && orderTable) {
            searchInput.addEventListener("keyup", function() {
                const filter = this.value.toLowerCase();
                const rows = orderTable.querySelectorAll("tbody tr");
                rows.forEach(row => {
                    const pelangganCell = row.cells[2]; // Kolom Pelanggan (index 2)
                    if (pelangganCell) {
                        row.style.display = pelangganCell.textContent.toLowerCase().includes(filter) ? "" : "none";
                    }
                });
            });
        }
        console.log("Admin Order: Search listener attached. Filter by button.");
    });

    // Kembalikan fungsi initiateAdminSnapPayment
    async function initiateAdminSnapPayment(orderId) {
        console.log(`[initiateAdminSnapPayment] Dipanggil untuk orderId: ${orderId}`);
        const snapMessageDiv = document.getElementById('adminSnapMessage');
        if (!snapMessageDiv) { console.error("Elemen adminSnapMessage tidak ditemukan!"); return; }
        snapMessageDiv.innerHTML = '<div class="alert alert-info">Meminta token pembayaran...</div>';

        let clickedButton = null;
        // Cara yang lebih aman untuk mendapatkan event, terutama jika fungsi dipanggil dari konteks lain
        const currentEvent = window.event || arguments.callee.caller.arguments[0]; // Coba dapatkan event
        if(currentEvent && currentEvent.target) {
             clickedButton = currentEvent.target.closest('button');
        }
        if(clickedButton) clickedButton.disabled = true;

        try {
            // Path ini relatif dari folder Admin ke folder Proses
            const response = await fetch(`../Proses/CreateSnapTokenForOrder.php?order_id=${orderId}`);
            if (!response.ok) { const errorText = await response.text(); throw new Error(`Gagal mendapatkan token: ${response.status}. ${errorText}`);}
            const result = await response.json();
            if (result.success && result.snap_token) {
                snapMessageDiv.innerHTML = '';
                 if (typeof snap === 'undefined') { throw new Error("Library Midtrans Snap.js tidak siap."); }
                snap.pay(result.snap_token, {
                    onSuccess: function(paymentResult){ console.log('Admin Payment Success:', paymentResult); snapMessageDiv.innerHTML = `<div class="alert alert-success">Pembayaran Order ID ${paymentResult.order_id} berhasil! Status: ${paymentResult.transaction_status}. Reload...</div>`; setTimeout(() => { window.location.reload(); }, 3000); },
                    onPending: function(paymentResult){ console.log('Admin Payment Pending:', paymentResult); snapMessageDiv.innerHTML = `<div class="alert alert-warning">Pembayaran Order ID ${paymentResult.order_id} tertunda. Status: ${paymentResult.transaction_status}.</div>`; if(clickedButton) clickedButton.disabled = false; },
                    onError: function(paymentResult){ console.error('Admin Payment Error:', paymentResult); snapMessageDiv.innerHTML = `<div class="alert alert-danger">Pembayaran Order ID ${paymentResult.order_id} gagal. Pesan: ${paymentResult.status_message || 'Error'}.</div>`; if(clickedButton) clickedButton.disabled = false; },
                    onClose: function(){ console.log('Admin Snap popup closed.'); if (!snapMessageDiv.querySelector('.alert-success') && !snapMessageDiv.querySelector('.alert-warning') && !snapMessageDiv.querySelector('.alert-danger')) { snapMessageDiv.innerHTML = '<div class="alert alert-secondary">Popup pembayaran ditutup.</div>'; } if(clickedButton) clickedButton.disabled = false; }
                });
            } else { throw new Error(result.message || 'Token Snap tidak diterima.'); }
        } catch (error) { console.error('Error saat initiateAdminSnapPayment:', error); snapMessageDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`; if(clickedButton) clickedButton.disabled = false; }
    }
</script>
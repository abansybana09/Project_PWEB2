<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Koneksi.php';

if (!isset($conn)) {
    die('<div class="alert alert-danger">Koneksi database gagal. Pastikan file Koneksi.php ada dan benar.</div>');
}


$query_menu = mysqli_query($conn, "SELECT id, foto, nama_menu, deskripsi_menu, harga, stok FROM tb_daftarmenu ORDER BY id DESC");

$num_rows = 0;

if (!$query_menu) {
    $error_message = mysqli_error($conn);
} else {
    $num_rows = mysqli_num_rows($query_menu);
}
?>
<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            Halaman Menu Makanan
        </div>
        <div class="card-body">

            <?php
            if (isset($_SESSION['status_message'])) {
                $message = $_SESSION['status_message'];
                echo '<div class="alert alert-' . htmlspecialchars($message['type']) . ' alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($message['text']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['status_message']);
            }

            if (isset($error_message) && !empty($error_message)) {
                echo '<div class="alert alert-danger" role="alert">';
                echo 'Gagal mengambil data menu: ' . htmlspecialchars($error_message);
                echo '</div>';
            }
            ?>

            <div class="row mb-3">
                <div class="col d-flex justify-content-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMenu">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Menu
                    </button>
                </div>
            </div>

            <!-- Modal Tambah Menu -->
            <div class="modal fade" id="modalTambahMenu" tabindex="-1" aria-labelledby="modalTambahMenuLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahMenuLabel">Tambah Menu Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../Proses/Input_menu.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="tambahNamaMenu" class="form-label">Nama Menu</label>
                                    <input type="text" class="form-control" id="tambahNamaMenu" name="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tambahDeskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="tambahDeskripsi" name="deskripsi" rows="3"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tambahHarga" class="form-label">Harga (Rp)</label>
                                        <input type="number" class="form-control" id="tambahHarga" name="harga" min="0" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tambahStok" class="form-label">Stok</label>
                                        <input type="number" class="form-control" id="tambahStok" name="stok" min="0" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tambahFoto" class="form-label">Foto Menu</label>
                                    <input class="form-control" type="file" id="tambahFoto" name="foto" accept="image/*" required>
                                    <small class="text-muted">Upload gambar (format: jpg, jpeg, png, maks 5MB)</small>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="submit_menu_validate" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Akhir Modal Tambah Menu -->

            <?php
            if ($query_menu && $num_rows == 0):
            ?>
                <div class="alert alert-info" role="alert">
                    Belum ada data menu. Silakan tambahkan menu baru.
                </div>
            <?php
            elseif ($query_menu && $num_rows > 0):
            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Nama Menu</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Stok</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($query_menu)) {
                                $imgPathAdmin = '../img/menu/' . htmlspecialchars($row['foto']);
                            ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td>
                                        <img src="<?= $imgPathAdmin ?>"
                                            alt="<?= htmlspecialchars($row['nama_menu']) ?>"
                                            width="80"
                                            onerror="this.onerror=null; this.src='../img/placeholder.png'; this.alt='Gambar tidak ditemukan';"
                                            loading="lazy">
                                    </td>
                                    <td><?= htmlspecialchars($row['nama_menu']) ?></td>
                                    <td><?= htmlspecialchars($row['deskripsi_menu']) ?></td>
                                    <td>Rp <?= number_format((float)$row['harga'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['stok']) ?></td>
                                    <td>
                                        <!-- Tombol Edit -->
                                        <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditMenu<?= $row['id'] ?>" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <!-- Tombol Hapus -->
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapusMenu<?= $row['id'] ?>" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Edit Menu untuk baris ini -->
                                <div class="modal fade" id="modalEditMenu<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalEditMenuLabel<?= $row['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalEditMenuLabel<?= $row['id'] ?>">Edit Menu: <?= htmlspecialchars($row['nama_menu']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Form action ke ../Proses/Edit_menu.php -->
                                                <form action="../Proses/Edit_menu.php" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($row['foto']) ?>">

                                                    <div class="mb-3 text-center">
                                                        <img src="<?= $imgPathAdmin ?>" alt="Foto Lama" width="150" class="mb-2 img-thumbnail" onerror="this.onerror=null; this.src='../img/placeholder.png'; this.alt='Gambar lama tidak ditemukan';">
                                                        <br><small>Foto Saat Ini</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="editNamaMenu<?= $row['id'] ?>" class="form-label">Nama Menu</label>
                                                        <input type="text" class="form-control" id="editNamaMenu<?= $row['id'] ?>" name="nama" value="<?= htmlspecialchars($row['nama_menu']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editDeskripsi<?= $row['id'] ?>" class="form-label">Deskripsi</label>
                                                        <textarea class="form-control" id="editDeskripsi<?= $row['id'] ?>" name="deskripsi" rows="3"><?= htmlspecialchars($row['deskripsi_menu']) ?></textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editHarga<?= $row['id'] ?>" class="form-label">Harga (Rp)</label>
                                                            <input type="number" class="form-control" id="editHarga<?= $row['id'] ?>" name="harga" min="0" value="<?= htmlspecialchars($row['harga']) ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editStok<?= $row['id'] ?>" class="form-label">Stok</label>
                                                            <input type="number" class="form-control" id="editStok<?= $row['id'] ?>" name="stok" min="0" value="<?= htmlspecialchars($row['stok']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editFoto<?= $row['id'] ?>" class="form-label">Ganti Foto Menu (Opsional)</label>
                                                        <input class="form-control" type="file" id="editFoto<?= $row['id'] ?>" name="foto" accept="image/*">
                                                        <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. (Maks 5MB)</small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="submit_menu_validate" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Akhir Modal Edit Menu -->

                                <!-- Modal Hapus Menu untuk baris ini -->
                                <div class="modal fade" id="modalHapusMenu<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalHapusMenuLabel<?= $row['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalHapusMenuLabel<?= $row['id'] ?>">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus menu "<b><?= htmlspecialchars($row['nama_menu']) ?></b>"? <br>
                                                <small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <!-- Form action ke ../Proses/Delete_menu.php -->
                                                <form action="../Proses/Delete_menu.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($row['foto']) ?>">
                                                    <button type="submit" name="submit_menu_validate" class="btn btn-danger">Ya, Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Akhir Modal Hapus Menu -->

                            <?php } // Akhir loop while
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; // Akhir kondisi Cek $query_menu dan $num_rows
            ?>
        </div> <!-- Akhir card-body -->
    </div> <!-- Akhir card -->
</div> <!-- Akhir col-lg-9 -->
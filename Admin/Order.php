<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "Koneksi.php";

$query = mysqli_query($conn, "SELECT * FROM tb_order ORDER BY id ASC");
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
            if (isset($_SESSION['status_message'])) {
                $message = $_SESSION['status_message'];
                echo '<div class="alert alert-' . htmlspecialchars($message['type']) . ' alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($message['text']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['status_message']);
            }

            if (isset($query_error) && !empty($query_error) && !isset($_SESSION['status_message'])) {
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
                                <th scope="col">Jumlah Pesanan</th>
                                <th scope="col">Total Harga</th>
                                <th scope="col">Pembayaran</th>
                                <th scope="col">CRUD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $display_id = 1;
                            foreach ($result as $row) :
                            ?>
                                <tr>
                                    <th scope="row"><?= $display_id++ ?></th>
                                    <td><?= htmlspecialchars($row['pelanggan']) ?></td>
                                    <td><?= htmlspecialchars($row['nohp']) ?></td>
                                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['pesanan'])) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah_pesan']) ?></td>
                                    <td><?= htmlspecialchars($row['total_harga']) ?></td>
                                    <td><?= htmlspecialchars($row['pembayaran']) ?></td>
                                    <td>
                                        <div class="d-flex">
                                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ViewData<?= $row['id'] ?>"><i class="bi bi-eye"></i></button>
                                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#EditData<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#HapusData<?= $row['id'] ?>"><i class="bi bi-trash3"></i></button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- View Order Modal -->
                                <div class="modal fade" id="ViewData<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Order</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input disabled type="text" class="form-control" value="<?= htmlspecialchars($row['pelanggan']) ?>">
                                                            <label>Nama Pelanggan</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input disabled type="text" class="form-control" value="<?= htmlspecialchars($row['nohp']) ?>">
                                                            <label>No HP</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-floating mb-3">
                                                            <input disabled type="text" class="form-control" value="<?= htmlspecialchars($row['alamat']) ?>">
                                                            <label>Alamat</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-floating mb-3">
                                                            <textarea disabled class="form-control" style="height: 100px"><?= htmlspecialchars($row['pesanan']) ?></textarea>
                                                            <label>Pesanan</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-floating mb-3">
                                                            <input disabled type="text" class="form-control" value="<?= htmlspecialchars($row['jumlah_pesan']) ?>">
                                                            <label>Jumlah Pesanan</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-floating mb-3">
                                                            <input disabled type="text" class="form-control" value="<?= htmlspecialchars($row['total_harga']) ?>">
                                                            <label>Total Harga</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-floating mb-3">
                                                            <input disabled type="text" class="form-control" value="<?= htmlspecialchars($row['pembayaran']) ?>">
                                                            <label>Pembayaran</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Order Modal -->
                                <div class="modal fade" id="EditData<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Order</h5>
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
                                                                <input type="text" class="form-control" name="total_harga" required value="<?= htmlspecialchars($row['total_harga']) ?>">
                                                                <label>Total Harga</label>
                                                                <div class="invalid-feedback">Total harga wajib diisi.</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-floating mb-3">
                                                                <select class="form-select" name="pembayaran" required>
                                                                    <option value="" disabled <?= empty($row['pembayaran']) ? 'selected' : '' ?>>Pilih Status Pembayaran</option>
                                                                    <option value="Ya" <?= ($row['pembayaran'] == 'Ya') ? 'selected' : '' ?>>Ya</option>
                                                                    <option value="Tidak" <?= ($row['pembayaran'] == 'Tidak') ? 'selected' : '' ?>>Tidak</option>
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

                                <!-- Delete Order Modal -->
                                <div class="modal fade" id="HapusData<?= $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="exampleModalLabel<?= $row['id'] ?>">Hapus Order</h1>
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
</div> <!-- Akhir div.col-lg-9 -->


<script>
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
    })()

    // Script Search
    document.addEventListener('DOMContentLoaded', (event) => {
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("keyup", function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll("#orderTable tbody tr");

                rows.forEach(row => {
                    const pelangganCell = row.cells[1];
                    if (pelangganCell) {
                        const pelanggan = pelangganCell.textContent.toLowerCase();
                        row.style.display = pelanggan.includes(filter) ? "" : "none";
                    }
                });
            });
        }
    });
</script>
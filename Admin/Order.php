<?php
include "Koneksi.php";
$query = mysqli_query($conn, "SELECT * FROM tb_order");
$result = [];
while ($record = mysqli_fetch_array($query)) {
    $result[] = $record;
}
?>

<div class="col-lg mt-2">
    <div class="card">
        <div class="card-header">
            Daftar Orderan
        </div>
        <div class="card-body">

            <?php if (empty($result)) : ?>
                <p>Data Order Tidak Ada.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <div class="d-flex justify-content-end">
                        <div class="mb-3" style="width: 200px;">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari Pelanggan">
                        </div>
                    </div>
                    <table class="table table-hover">
                        <thead>
                            <tr class="text-nowrap">
                                <th scope="col">Id</th>
                                <th scope="col">Pelanggan</th>
                                <th scope="col">No HP</th>
                                <th scope="col">Alamat</th>
                                <th scope="col">Pesanan</th>
                                <th scope="col">Jumlah Pesanan</th>
                                <th scope="col">Total Harga</th>
                                <th scope="col">CRUD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id = 1;
                            foreach ($result as $row) :
                            ?>
                                <tr>
                                    <th scope="row"><?= $id++ ?></th>
                                    <td><?= $row['pelanggan'] ?></td>
                                    <td><?= $row['nohp'] ?></td>
                                    <td><?= $row['alamat'] ?></td>
                                    <td><?= $row['pesanan'] ?></td>
                                    <td><?= $row['jumlah_pesan'] ?></td>
                                    <td><?= $row['total_harga'] ?></td>
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
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form class="needs-validation" novalidate>
                                                    <div class="form-floating mb-3">
                                                        <input disabled type="text" class="form-control" value="<?= $row['pelanggan'] ?>">
                                                        <label>Nama Pelanggan</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input disabled type="text" class="form-control" value="<?= $row['nohp'] ?>">
                                                        <label>No HP</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input disabled type="text" class="form-control" value="<?= $row['alamat'] ?>">
                                                        <label>Alamat</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea disabled class="form-control" style="height: 100px"><?= $row['pesanan'] ?></textarea>
                                                        <label>Pesanan</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea disabled class="form-control" style="height: 100px"><?= $row['jumlah_pesan'] ?></textarea>
                                                        <label>Jumlah Pesanan</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input disabled type="text" class="form-control" value="<?= $row['total_harga'] ?>">
                                                        <label>Total Harga</label>
                                                    </div>
                                                </form>
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
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="../Proses/Edit_order.php" method="POST" class="needs-validation" novalidate>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="pelanggan" required value="<?= $row['pelanggan'] ?>">
                                                        <label>Nama Pelanggan</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="nohp" required value="<?= $row['nohp'] ?>">
                                                        <label>No HP</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="alamat" required value="<?= $row['alamat'] ?>">
                                                        <label>Alamat</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" style="height: 100px" name="pesanan" required><?= $row['pesanan'] ?></textarea>
                                                        <label>Pesanan</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" style="height: 100px" name="jumlah_pesan" required><?= $row['jumlah_pesan'] ?></textarea>
                                                        <label>Jumlah Pesanan</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="total_harga" required value="<?= $row['total_harga'] ?>">
                                                        <label>Total Harga</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Order Modal -->
                                <div class="modal fade" id="HapusData<?php echo $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Order</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="col-lg-12">
                                                &nbsp;Apakah Anda yakin ingin menghapus order dari <b><?php echo $row['pelanggan'] ?></b>?
                                            </div>
                                            <div class="modal-body">
                                                <form class="needs-validation" novalidate action="../Proses/Delete_order.php" method="POST">
                                                    <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
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
        </div>
    </div>
</div>

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
</script>

<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll("table tbody tr");

        rows.forEach(row => {
            const pelanggan = row.cells[1].textContent.toLowerCase();
            row.style.display = pelanggan.includes(filter) ? "" : "none";
        });
    });
</script>
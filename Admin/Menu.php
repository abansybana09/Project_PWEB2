<?php
include "Koneksi.php";
$query = mysqli_query($conn, "SELECT * FROM tb_daftarmenu");
while ($record = mysqli_fetch_array($query)) {
    $result[] = $record;
}
?>

<div class="col-lg mt-2">
    <div class="card">
        <div class="card-header">
            Daftar Menu
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col d-flex justify-content-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#TambahMenu"> Tambah Menu</button>
                </div>
            </div>
            <!-- Tambah Menu -->
            <div class="modal fade" id="TambahMenu" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Menu</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate action="../Proses/Input_menu.php" method="POST" enctype="multipart/form-data">
                                <div class="input-group mb-3">
                                    <input type="file" class="form-control" id="floatingInput" placeholder="File" name="foto" required>
                                    <label class="input-group-text" for="floatingInput">Foto Menu</label>
                                    <div class="invalid-feedback">
                                        Masukan Foto Menu.
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="floatingInput" placeholder="Nama" name="nama" required>
                                    <label for="floatingInput">Nama Menu</label>
                                    <div class="invalid-feedback">
                                        Masukan Nama Menu.
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="floatingPassword" placeholder="Deskripsi" name="deskripsi" required>
                                        <label for="floatingInput">Deskripsi Menu</label>
                                        <div class="invalid-feedback">
                                            Masukan Deskripsi Menu.
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="floatingPassword" placeholder="Harga" name="harga" required>
                                            <label for="floatingInput">Harga</label>
                                            <div class="invalid-feedback">
                                                Masukan Harga.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-floating mb-3">
                                            <input type="number" class="form-control" id="floatingPassword" placeholder="Stok" name="stok" required>
                                            <label for="floatingInput">Stok</label>
                                            <div class="invalid-feedback">
                                                Masukan Jumlah Stok.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="submit_menu_validate" value="123">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            foreach ($result as $row) {
            ?>
                <!-- View Data -->
                <div class="modal fade" id="ViewData<?php echo $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">View Menu</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate action="../Proses/Input_menu.php" method="POST">
                                    <div class="form-floating mb-3">
                                        <input disabled type="text" class="form-control" id="floatingInput" placeholder="Nama" name="nama" value="<?php echo $row['nama_menu'] ?>">
                                        <label for="floatingInput">Nama Menu</label>
                                    </div>
                                    <div class="col">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" id="floatingPassword" placeholder="Deskripsi" name="deskripsi" value="<?php echo $row['deskripsi_menu'] ?>">
                                            <label for="floatingInput">Deskripsi Menu</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="form-floating mb-3">
                                                <input disabled type="text" class="form-control" id="floatingPassword" placeholder="Harga" name="harga" value="<?php echo $row['harga'] ?>">
                                                <label for="floatingInput">Harga</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-floating mb-3">
                                                <input disabled type="text" class="form-control" id="floatingPassword" placeholder="Stok" name="stok" value="<?php echo $row['stok'] ?>">
                                                <label for="floatingInput">Stok</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Data -->
                <div class="modal fade" id="EditData<?php echo $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Menu</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate action="../Proses/Edit_menu.php" method="POST" enctype="multipart/form-data">
                                    <div class="input-group mb-3">
                                        <input type="hidden" value="<?php echo $row['id'] ?>" name="id">
                                        <input type="file" class="form-control" id="floatingInput" placeholder="File" name="foto">
                                        <label class="input-group-text" for="floatingInput">Foto Menu (Opsional)</label>
                                        <div class="invalid-feedback">
                                            Masukan Foto Menu.
                                        </div>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="floatingInput" placeholder="Nama" name="nama" required value="<?php echo $row['nama_menu'] ?>">
                                        <label for="floatingInput">Nama Menu</label>
                                        <div class="invalid-feedback">
                                            Masukan Nama Menu.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="floatingPassword" placeholder="Deskripsi" name="deskripsi" required value="<?php echo $row['deskripsi_menu'] ?>">
                                            <label for="floatingInput">Deskripsi Menu</label>
                                            <div class="invalid-feedback">
                                                Masukan Deskripsi Menu.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="Harga" name="harga" required value="<?php echo $row['harga'] ?>">
                                                <label for="floatingInput">Harga</label>
                                                <div class="invalid-feedback">
                                                    Masukan Harga.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-floating mb-3">
                                                <input type="number" class="form-control" id="floatingPassword" placeholder="Stok" name="stok" required value="<?php echo $row['stok'] ?>">
                                                <label for="floatingInput">Stok</label>
                                                <div class="invalid-feedback">
                                                    Masukan Jumlah Stok.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" name="submit_menu_validate" value="123">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hapus Data -->
                <div class="modal fade" id="HapusData<?php echo $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Menu</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="col-lg-12">
                                &nbsp;Apakah Anda Yakin Ingin Menghapus Menu <b><?php echo $row['nama_menu'] ?></b>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" novalidate action="../Proses/Delete_menu.php" method="POST">
                                    <input type="hidden" value="<?php echo $row['id'] ?>" name="id">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger" name="submit_menu_validate" value="123">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
            }
            if (empty($result)) {
                echo "Data Menu Tidak Ada";
            } else {
            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Id</th>
                                <th scope="col">Foto Menu</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Deskripsi Menu</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Stok</th>
                                <th scope="col">CRUD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id = 1;
                            foreach ($result as $row) {
                            ?>
                                <tr>
                                    <th scope="row"><?php echo $id++ ?></th>
                                    <td>
                                        <img src="../img/<?php echo $row['foto'] ?>"
                                            class="img-thumbnail"
                                            alt="Foto Menu"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td><?php echo $row['nama_menu'] ?></td>
                                    <td><?php echo $row['deskripsi_menu'] ?></td>
                                    <td><?php echo $row['harga'] ?></td>
                                    <td><?php echo $row['stok'] ?></td>
                                    <td>
                                        <div class="d-flex">
                                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ViewData<?php echo $row['id'] ?>"><i class="bi bi-eye"></i></button>
                                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#EditData<?php echo $row['id'] ?>"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#HapusData<?php echo $row['id'] ?>"><i class="bi bi-trash3"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php
            }
            ?>
        </div>
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
<?php
// File: PROJECR2/models/MenuModel.php

class MenuModel
{
    private $db;
    private $connectionError = null;
    public function __construct()
    {
        $koneksiPath = __DIR__ . '/../Admin/Koneksi.php';

        if (!file_exists($koneksiPath)) {
            $this->connectionError = "Error: File Koneksi.php tidak ditemukan di " . $koneksiPath;
            error_log($this->connectionError);
            $this->db = null;
            return;
        }

        require_once $koneksiPath;

        if (!isset($conn)) {
            $this->connectionError = "Error: Variabel koneksi global (\$conn) tidak ditemukan setelah include Koneksi.php.";
            error_log($this->connectionError);
            $this->db = null;
        } elseif (!$conn || !($conn instanceof mysqli) || mysqli_connect_errno()) {
            $this->connectionError = "Koneksi database gagal: " . mysqli_connect_error();
            error_log($this->connectionError);
            $this->db = null;
        } else {
            $this->db = $conn;
            if (!mysqli_set_charset($this->db, "utf8mb4")) {
                error_log("Error loading character set utf8mb4: " . mysqli_error($this->db));
            }
        }
    }

    /**
     * Mengambil semua item menu dari database.
     * @return array Array berisi data menu, atau array kosong jika gagal atau tidak ada data.
     */
    public function getAllMenuItems()
    {
        if (!$this->db) {

            echo "<div class='alert alert-danger'>Error Model: Tidak dapat terhubung ke database. " . htmlspecialchars($this->connectionError ?? 'Detail tidak diketahui.') . "</div>";
            return [];
        }

        $menuItems = [];
        $sql = "SELECT id, foto, nama_menu, deskripsi_menu, harga, stok FROM tb_daftarmenu ORDER BY nama_menu ASC";

        $result = mysqli_query($this->db, $sql);

        if (!$result) {
            $queryError = "Error query menu: " . mysqli_error($this->db);
            error_log($queryError);
            echo "<div class='alert alert-danger'>Error Model: Gagal menjalankan query menu. " . htmlspecialchars($queryError) . "</div>";
            return [];
        }

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $menuItems[] = [
                    'id'          => $row['id'],
                    'title'       => $row['nama_menu'],
                    'image'       => 'img/menu/' . rawurlencode(htmlspecialchars($row['foto'])),
                    'description' => $row['deskripsi_menu'],
                    'price'       => (int)$row['harga'],
                    'stock'       => (int)$row['stok'],
                ];
            }
        }
        mysqli_free_result($result);

        return $menuItems;
    }
}

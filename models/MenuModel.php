<?php
// File: PROJECR2/models/MenuModel.php

class MenuModel {
    private $db; // Properti untuk menyimpan objek koneksi database
    private $connectionError = null; // Untuk menyimpan pesan error koneksi

    /**
     * Konstruktor: Membuat koneksi database saat objek model dibuat.
     */
    public function __construct() {
        // Sertakan file koneksi HANYA SEKALI.
        // Path ini relatif dari file MenuModel.php ke folder Admin.
        // Asumsi: 'models' dan 'Admin' berada di bawah 'PROJECR2'.
        $koneksiPath = __DIR__ . '/../Admin/Koneksi.php';

        if (!file_exists($koneksiPath)) {
            $this->connectionError = "Error: File Koneksi.php tidak ditemukan di " . $koneksiPath;
            error_log($this->connectionError);
            $this->db = null;
            return; // Hentikan konstruktor jika file koneksi tidak ada
        }

        require_once $koneksiPath;

        // Akses variabel koneksi global $conn yang didefinisikan di Koneksi.php
        // Periksa apakah variabel $conn ada setelah include
        if (!isset($conn)) {
            $this->connectionError = "Error: Variabel koneksi global (\$conn) tidak ditemukan setelah include Koneksi.php.";
            error_log($this->connectionError);
            $this->db = null;
        }
        // Periksa apakah $conn adalah resource koneksi yang valid dan tidak ada error
        elseif (!$conn || !($conn instanceof mysqli) || mysqli_connect_errno()) {
            $this->connectionError = "Koneksi database gagal: " . mysqli_connect_error();
            error_log($this->connectionError); // Log error
            $this->db = null;
        } else {
            // Koneksi berhasil, simpan ke properti $db
            $this->db = $conn;
            // Pastikan charset sudah di set (bisa juga di Koneksi.php)
            if (!mysqli_set_charset($this->db, "utf8mb4")) {
                 error_log("Error loading character set utf8mb4: " . mysqli_error($this->db));
            }
        }
    }

    /**
     * Mengambil semua item menu dari database.
     * @return array Array berisi data menu, atau array kosong jika gagal atau tidak ada data.
     */
    public function getAllMenuItems() {
        // 1. Periksa Error Koneksi dari Konstruktor
        if (!$this->db) {
            // Tampilkan pesan error koneksi jika ada saat konstruksi
            // Pesan ini akan muncul di halaman frontend jika koneksi gagal
            echo "<div class='alert alert-danger'>Error Model: Tidak dapat terhubung ke database. " . htmlspecialchars($this->connectionError ?? 'Detail tidak diketahui.') . "</div>";
            return []; // Kembalikan array kosong
        }

        $menuItems = [];
        // 2. Query untuk mengambil data (pastikan nama kolom di DB benar)
        // Kolom: id, foto, nama_menu, deskripsi_menu, harga, stok
        $sql = "SELECT id, foto, nama_menu, deskripsi_menu, harga, stok FROM tb_daftarmenu ORDER BY nama_menu ASC"; // Urutkan berdasarkan nama

        $result = mysqli_query($this->db, $sql);

        // 3. Periksa Error Query
        if (!$result) {
            $queryError = "Error query menu: " . mysqli_error($this->db);
            error_log($queryError); // Log error query ke log server
            // Tampilkan pesan error query di halaman frontend
            echo "<div class='alert alert-danger'>Error Model: Gagal menjalankan query menu. " . htmlspecialchars($queryError) . "</div>";
            return []; // Kembalikan array kosong
        }

        // 4. Proses Hasil Query jika tidak ada error dan ada data
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Mapping data dari kolom DB ke key array yang konsisten
                // Ini adalah key yang akan digunakan di Controller dan View
                $menuItems[] = [
                    'id'          => $row['id'],
                    'title'       => $row['nama_menu'],
                    // Path gambar relatif dari file index.php di root proyek
                    'image'       => 'img/menu/' . rawurlencode(htmlspecialchars($row['foto'])), // rawurlencode jika nama file aneh
                    'description' => $row['deskripsi_menu'],
                    'price'       => (int)$row['harga'], // Casting ke integer
                    'stock'       => (int)$row['stok'],  // Casting ke integer
                ];
            }
        }
        // Jika tidak ada baris data, $menuItems akan tetap kosong (sesuai inisialisasi)

        mysqli_free_result($result); // Bebaskan memori hasil query

        return $menuItems; // Kembalikan array data menu (bisa kosong)
    }

    /**
     * Destruktor (Opsional untuk mysqli)
     * Dipanggil saat objek tidak lagi direferensikan.
     * Bisa digunakan untuk menutup koneksi secara eksplisit.
     */
    // public function __destruct() {
    //     // Periksa apakah $this->db adalah objek mysqli yang valid sebelum menutup
    //     if ($this->db && $this->db instanceof mysqli) {
    //         mysqli_close($this->db);
    //         // echo "Koneksi DB ditutup oleh destruktor MenuModel.<br>"; // Untuk debug
    //     }
    // }
}
?>
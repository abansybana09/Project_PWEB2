<?php
// File: models/MenuModel.php

class MenuModel {
    // Data statis untuk menu makanan
    public function getAllMenuItems() {
        return [
            [
                'title' => 'Ayam Goreng',
                'image' => 'img/a.jpg',
                'price' => 10000,
                'whatsapp_link' => 'https://wa.me/6289630152631'
            ],

            [
                'title' => 'Ayam Bakar',
                'image' => 'img/bakar2.jpg',
                'price' => 10000,
                'whatsapp_link' => 'https://wa.me/6289630152631'
            ],
            // ... item lainnya
        ];
    }
    // Untuk mengambil data dari database (opsional)
    /*
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAllMenuItems() {
        $query = "SELECT * FROM menu_items WHERE is_active = 1 ORDER BY category, name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    */
}
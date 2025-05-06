<?php
require_once __DIR__ . '/../models/Lokasi.php';
class LokasiController {
    public function showBranches() {
        // Data cabang statis (hardcoded)
        $data = [
            [
                'image' => 'img/cijoho.png',
                'title' => 'Cabang Kuningan',
                'address' => 'Jl. Jendral Sudirman No. 45',
                'phone' => '08123456789',
                'open_hours' => '10:00 - 22:00',
                'maps_url' => 'https://maps.app.goo.gl/Fa2iYDpqLuMjZK7eA' // ganti dengan koordinat asli
            ],
            [
                'image' => 'img/kuningan2.png',
                'title' => 'Cabang Cijoho',
                'address' => 'Jl. MH Thamrin No. 12',
                'phone' => '08129876543',
                'open_hours' => '09:00 - 21:00',
                'maps_url' => 'https://maps.app.goo.gl/PEYE9eVm49wx6GVn7' // ganti juga sesuai lokasi asli
            ]
        ];
        
        
        require_once __DIR__ . '/../views/Lokasi/index.php';
    }
}
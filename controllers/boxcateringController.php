<?php
require_once __DIR__ . '../../models/BoxCatering.php';

class boxcateringController {
    public function index() {
        $box = new BoxCatering();
        $data = $box->getMenu();

        // Berikan data ke view
        include __DIR__ . '../../views/boxcatering/index.php';
    }
    
}

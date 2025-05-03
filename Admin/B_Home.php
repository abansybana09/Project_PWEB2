<?php
    if (isset($_GET['x']) && $_GET['x'] == 'Home') {
        $page = 'Content.php';
        include 'Main.php';
    } else if (isset($_GET['x']) && $_GET['x'] == 'Menu') {
        $page = 'Menu.php';
        include 'Main.php';
    } else if (isset($_GET['x']) && $_GET['x'] == 'Order') {
        $page = 'Order.php';
        include 'Main.php';
    }else if (isset($_GET['x']) && $_GET['x'] == 'Login') {
        include 'Login.php';
    }else if (isset($_GET['x']) && $_GET['x'] == 'Logout') {
        include 'ProsesLogout.php';
    }else {
        $page = 'Content.php';
        include 'Main.php';
    }
?>
<div class="col-lg-3">
    <nav class="navbar navbar-expand-lg bg-body-tertiary rounded-2 border mt-2">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header" style="width: 200px;">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Offcanvas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav nav-pills flex-column justify-content-end flex-grow-1">
                        <li class="nav-item">
                            <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'Home' || !isset($_GET['x'])) ? 'active link-light' : 'link-dark'; ?>" aria-current="page" href="Home">
                                <i class="bi bi-house-door"></i> Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'Menu') ? 'active link-light' : 'link-dark'; ?>" href="Menu">
                                <i class="bi bi-card-list"></i> Daftar Menu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link ps-2 <?php echo (isset($_GET['x']) && $_GET['x'] == 'Order') ? 'active link-light' : 'link-dark'; ?>" href="Order">
                                <i class="bi bi-cart3"></i> Order</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</div>
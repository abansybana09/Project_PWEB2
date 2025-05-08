<nav class="navbar navbar-expand navbar-dark sticky-top" style="background-color: #8B0000;">
    <div class="container-lg">
        <a class="navbar-brand" href="../index.php?page=project">
            <i class="bi bi-shop-window"></i> Ayam Bakar Mang Oman
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo isset($hasil['username']) ? htmlspecialchars($hasil['username'], ENT_QUOTES, 'UTF-8') : 'Guest'; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2">
                        <li><a class="dropdown-item" href="Logout"><i class="bi bi-door-open"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/dashboard">WhatsApp App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReports" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Reports
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownReports">
                        <li><a class="dropdown-item" href="/history/individual">Individual History</a></li>
                        <li><a class="dropdown-item" href="/history/bulk">Bulk History</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownManage" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Manage
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownManage">
                        <li><a class="dropdown-item" href="/scheduled-messages">Scheduled Messages</a></li>
                        <li><a class="dropdown-item" href="/templates">Manage Templates</a></li>
                        <li><a class="dropdown-item" href="/api-settings">API Settings</a></li>
                    </ul>
                </li>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'super_user' || $_SESSION['role'] === 'administrator')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/users">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/packages">Manage Packages</a>
                    </li>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'reseller'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/users">Manage Users</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProfile" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['name'] ?: $_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark"
                        aria-labelledby="navbarDropdownProfile">
                        <li>
                            <h6 class="dropdown-header text-info">
                                Role: <?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?>
                            </h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="/profile/password">
                                <i class="bi bi-key me-2"></i> Change Password
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
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
                <li class="nav-item">
                    <a class="nav-link" href="/history/individual">Individual History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/history/bulk">Bulk History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/api-settings">API Settings</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'client'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/users">Manage Users</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/logout">Logout (
                        <?php echo $_SESSION['username']; ?>)
                    </a></li>
            </ul>
        </div>
    </div>
</nav>
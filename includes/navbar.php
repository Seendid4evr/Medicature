<?php

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="nav-brand-row" style="display:flex;justify-content:space-between;align-items:center;width:100%;">
        <a href="../pages/dashboard.php" class="nav-brand" style="font-size:1.3rem;font-weight:700;color:var(--primary-color);text-decoration:none;">💊 Medicature</a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" onclick="document.getElementById('navMenu').classList.toggle('open')">☰</button>
    </div>
    <div class="nav-menu" id="navMenu">
        <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">🏠 Dashboard</a>
        <a href="medicines.php" class="<?php echo $currentPage === 'medicines.php' ? 'active' : ''; ?>">💊 Medicines</a>
        <a href="search.php" class="<?php echo $currentPage === 'search.php' ? 'active' : ''; ?>">🔍 Search</a>
        <a href="calculators.php" class="<?php echo $currentPage === 'calculators.php' ? 'active' : ''; ?>">🧮 Calculators</a>
        <a href="family.php" class="<?php echo $currentPage === 'family.php' ? 'active' : ''; ?>">👨‍👩‍👦 Family</a>
        <a href="reports.php" class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">📊 Reports</a>
        <a href="pharmacy.php" class="<?php echo $currentPage === 'pharmacy.php' ? 'active' : ''; ?>" style="<?php echo $currentPage !== 'pharmacy.php' ? 'color:#16a34a;font-weight:600;' : ''; ?>">🏪 Pharmacy</a>
        <a href="buy_medicine.php" class="<?php echo $currentPage === 'buy_medicine.php' ? 'active' : ''; ?>" style="color:#0284c7;font-weight:600;">🛒 Buy Medicine</a>
        <a href="symptom_checker.php" class="<?php echo $currentPage === 'symptom_checker.php' ? 'active' : ''; ?>" style="<?php echo $currentPage !== 'symptom_checker.php' ? 'color:#7c3aed;font-weight:600;' : ''; ?>">🤖 AI Triage</a>
        <a href="profile.php" class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">👤 Profile</a>
        <?php if (getUserIsAdmin()): ?>
        <a href="../admin/index.php" style="color:#f59e0b;font-weight:600;" title="Admin Panel">⚙️ Admin</a>
        <?php endif; ?>
        <a href="../logout.php" style="color:var(--error-color);">🚪 Logout</a>
    </div>
</nav>


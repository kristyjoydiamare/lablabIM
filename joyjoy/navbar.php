<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background-color:  #ecd5e7;
        padding: 0;
        min-height: 100vh;
        box-sizing: border-box;
    }
    .main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 15px;
    }

    .custom-navbar {
        background-color: #fcfcfc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 40px;
        height: 70px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    .nav-brand-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .nav-brand-text {
        color: #131212;
        font-size: 22px;
        font-weight: bold;
        margin: 0;
    }
    .nav-links {
        display: flex;
        gap: 20px;
        list-style: none;
        margin: 0;
        padding: 0;
        position: absolute;
        left: 80%;
        transform: translateX(-50%);
    }
    .nav-links a {
        color: #131212;
        text-decoration: none;
        font-size: 15px;
        font-weight: bold;
        transition: color 0.2s;
    }
    .nav-links a:hover, .nav-links .active-link {
        color: #161616;
    }
</style>

<nav class="custom-navbar">
    <div class="nav-brand-container">
        <span class="nav-brand-text">Inventory System</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active-link' : '' ?>">Products</a></li>
        <li><a href="reports.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'report.php') ? 'active-link' : '' ?>">Reports</a></li>
        <li><a href="add.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'add.php') ? 'active-link' : '' ?>">Add Product</a></li>
    </ul>
</nav>
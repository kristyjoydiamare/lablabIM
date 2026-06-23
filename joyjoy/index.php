<?php
require_once 'auth.php';
requireLogin(); 
require_once 'config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// 1. Build and Prepare the Products Query securely
$sql = "
SELECT p.*, c.name AS category_name, s.name AS supplier_name
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN suppliers s ON p.supplier_id = s.id
WHERE 1=1
";

$params = [];
$types = "";

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($category) {
    $sql .= " AND c.name = ?";
    $params[] = $category;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Products query preparation failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Products query execution failed: " . $stmt->error);
}

$products = $stmt->get_result();


// 2. Fetch Categories (with explicit error checking)
$categories = $conn->query("SELECT DISTINCT name FROM categories ORDER BY name");
if (!$categories) {
    die("Categories Query Failed: " . $conn->error . ". check if the 'categories' table and 'name' column exist.");
}


// 3. Fetch Stats (with explicit error checking)
$stats_query = $conn->query("
SELECT 
COUNT(*) AS total,
SUM(stock) AS total_stock,
SUM(price * stock) AS total_value,
SUM(CASE WHEN stock < 20 THEN 1 ELSE 0 END) AS low_stock
FROM products
");

if (!$stats_query) {
    die("Stats Query Failed: " . $conn->error);
}

$stats = $stats_query->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory System</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ecd5e7;
            margin: 0;
            padding: 0;
            color: #000000;
        }
        
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 40px auto;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        h2 {
            font-size: 1.75rem;
            color: #000000;
            margin: 0;
        }

        .btn-add {
            background: #6366f1;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgb(99 102 241 / 0.15);
            transition: background 0.2s;
        }
        .btn-add:hover { background: #4f46e5; }

        .card-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.02);
            text-align: left;
        }
        .card h3 {
            margin: 0 0 6px 0;
            font-size: 1.75rem;
            color: #0f172a;
            font-weight: 700;
        }
        .card p {
            margin: 0;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
      
        .card.low-stock-alert {
            border-left: 4px solid #ef4444;
        }

        form {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            background: white;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        input, select {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #334155;
            outline: none;
            background-color: #fff;
        }
        input { flex: 2; }
        select { flex: 1; }
        input:focus, select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
        }
        form button {
            padding: 10px 24px;
            background: #0f172a;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        form button:hover { background: #1e293b; }

        .table-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        th, td {
            padding: 16px 20px;
            text-align: left;
        }
        th {
            background: #e72e5c;
            color: #000000;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }
        tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease;
        }
        tr:last-child {
            border-bottom: none;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        
        tr.low {
            background-color: #eb9797;
        }
        tr.low td.stock-cell {
            color: #e40000;
            font-weight: 700;
        }

        .actions-cell {
            display: flex;
            gap: 16px;
        }
        .btn-edit {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .btn-edit:hover { text-decoration: underline; }
        
        .btn-delete {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: color 0.2s;
        }
        .btn-delete:hover {
            color: #b91c1c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">

    <div class="card-row">
        <div class="card">
            <h3><?= number_format($stats['total'] ?? 0) ?></h3>
            <p>Total Products</p>
        </div>
        <div class="card">
            <h3><?= number_format($stats['total_stock'] ?? 0) ?></h3>
            <p>Total Stock</p>
        </div>
        <div class="card">
            <h3><?= number_format($stats['total_value'] ?? 0, 2) ?></h3>
            <p>Inventory Value</p>
        </div>
        <div class="card <?= (($stats['low_stock'] ?? 0) > 0) ? 'low-stock-alert' : '' ?>">
            <h3 style="color: <?= (($stats['low_stock'] ?? 0) > 0) ? '#ef4444' : 'inherit' ?>;"><?= $stats['low_stock'] ?? 0 ?></h3>
            <p>Low Stock Items</p>
        </div>
    </div>

    <form method="GET">
        <input type="text" name="search" placeholder="Search product name or description..." value="<?= htmlspecialchars($search) ?>">

        <select name="category">
            <option value="">All Categories</option>
            <?php while($c = $categories->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($c['name']) ?>" <?= $category == $c['name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Filter</button>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr class="<?= $p['stock'] < 20 ? 'low' : '' ?>">
                    <td><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['supplier_name'] ?? '') ?></td>
                    <td><?= number_format($p['price'], 2) ?></td>
                    <td class="stock-cell"><?= $p['stock'] ?></td>
                    <td style="color: #000000; font-size: 0.85rem;"><?= $p['created_at'] ?? '' ?></td>
                    <td>
                        <div class="actions-cell">
                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                            <a href="delete.php?id=<?= $p['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
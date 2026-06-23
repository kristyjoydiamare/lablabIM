<?php
require_once 'auth.php';
requireAdmin(); 
require_once 'config.php';


$summary_sql = "SELECT 
                    COUNT(id) AS total_products, 
                    SUM(stock) AS total_items, 
                    SUM(price * stock) AS estimated_worth,
                    SUM(CASE WHEN stock < 20 THEN 1 ELSE 0 END) AS low_stock_count
                FROM products";
$summary_result = $conn->query($summary_sql);
$summary_row = $summary_result->fetch_assoc();

$category_sql = "SELECT c.id,
                        c.name, 
                        COUNT(p.id) AS products,
                        SUM(p.stock) AS total_stock,
                        SUM(p.price * p.stock) AS total_value,
                        AVG(p.price) AS avg_price
                 FROM categories c
                 LEFT JOIN products p ON c.id = p.category_id
                 GROUP BY c.id, c.name
                 ORDER BY total_value DESC";
$category_result = $conn->query($category_sql);


$supplier_sql = "SELECT s.id,
                        s.name, 
                        COUNT(p.id) AS products,
                        SUM(p.stock) AS total_stock
                 FROM suppliers s
                 LEFT JOIN products p ON s.id = p.supplier_id
                 GROUP BY s.id, s.name
                 ORDER BY total_stock DESC";
$supplier_result = $conn->query($supplier_sql);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Inventory Reports</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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

        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: 40px;
            margin-bottom: 16px;
        }

        .btn-back {
            background: #0f172a;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #1e293b;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
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
            color: #000000;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .card.low-stock-alert {
            border-left: 4px solid #ef4444;
        }

        .table-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            padding: 16px 20px;
            text-align: left;
        }

        th {
            background: #f1f5f9;
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

        .id-column {
            font-weight: 600;
            color: #000000;
            width: 80px;
        }

        .muted-text {
            color: #94a3b8;
            font-style: italic;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container">

        <div class="cards-grid">
            <div class="card">
                <h3><?= number_format($summary_row['total_products'] ?? 0); ?></h3>
                <p>Total Products</p>
            </div>
            <div class="card">
                <h3><?= number_format($summary_row['total_items'] ?? 0); ?></h3>
                <p>Total Stock</p>
            </div>
            <div class="card">
                <h3><?= number_format($summary_row['estimated_worth'] ?? 0, 2); ?></h3>
                <p>Inventory Value</p>
            </div>
            <div class="card <?= ($summary_row['low_stock_count'] > 0) ? 'low-stock-alert' : '' ?>">
                <h3 style="color: <?= ($summary_row['low_stock_count'] > 0) ? '#ef4444' : 'inherit' ?>;">
                    <?= $summary_row['low_stock_count'] ?? 0; ?>
                </h3>
                <p>Low Stock Items</p>
            </div>
        </div>

        <h2>Category Distributions</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="id-column">Category ID</th>
                        <th>Category Name</th>
                        <th>Distinct Products</th>
                        <th>Total Stock Volume</th>
                        <th>Average Price Point</th>
                        <th>Combined Market Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $category_result->fetch_assoc()): ?>
                        <tr>
                            <td class="id-column"><?= $row['id']; ?></td>
                            <td><strong><?= htmlspecialchars($row['name']); ?></strong></td>
                            <td><?= $row['products']; ?> items</td>
                            <td><?= $row['total_stock'] !== null ? number_format($row['total_stock']) : '<span class="muted-text">0</span>'; ?></td>
                            <td><?= number_format($row['avg_price'] ?? 0, 2); ?></td>
                            <td><strong><?= number_format($row['total_value'] ?? 0, 2); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h2>Supplier Analytics</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="id-column">Supplier ID</th>
                        <th>Supplier Name</th>
                        <th>Product Count</th>
                        <th>Total Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $supplier_result->fetch_assoc()): ?>
                        <tr>
                            <td class="id-column"><?= $row['id']; ?></td>
                            <td><strong><?= htmlspecialchars($row['name']); ?></strong></td>
                            <td><?= $row['products']; ?></td>
                            <td><?= $row['total_stock'] !== null ? number_format($row['total_stock']) : '<span class="muted-text">0</span>'; ?> </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>
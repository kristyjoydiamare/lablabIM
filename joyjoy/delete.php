<?php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_sql = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: index.php?msg=Product deleted successfully");
        exit;
    } else {
        die("Error deleting record: " . mysqli_error($conn));
    }
}


$sql = "SELECT p.*, c.name AS category 
        FROM products p 
        INNER JOIN categories c ON p.category_id = c.id 
        WHERE p.id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Product</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #ecd5e7;
            color: #000000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }
        .confirm-card {
            background: #ffffff;
            max-width: 500px;
            width: 100%;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
            border: 1px solid #e2e8f0;
        }
        h1 {
            color: #000000;
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 12px;
        }
        p {
            color: #000000;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .details-grid {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 28px;
            border: 1px solid #f1f5f9;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label { font-weight: 500; color: #000000; }
        .value { font-weight: 600; color: #000000; }
        .actions {
            display: flex;
            gap: 12px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-danger {
            background: #e9048a;
            color: white;
        }
        .btn-danger:hover { background: #e956c9; }
        .btn-secondary {
            background:  #ecd5e7;
            color: #475569;
        }
        .btn-secondary:hover { background: #cbd5e1; }
    </style>
</head>
<body>

<div class="confirm-card">
    <h1>Confirm Deletion</h1>
    
    <div class="details-grid">
        <div class="detail-row">
            <span class="label">Product Name</span>
            <span class="value"><?= htmlspecialchars($product['name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Category</span>
            <span class="value"><?= htmlspecialchars($product['category']); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Price</span>
            <span class="value"><?= number_format($product['price'], 2); ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Stock</span>
            <span class="value"><?= $product['stock']; ?> units</span>
        </div>
    </div>

    <form method="POST" class="actions">
        <a href="index.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
</div>

</body>
</html>
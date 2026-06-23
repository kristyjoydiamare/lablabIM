<?php
require_once 'config.php';

$categories = $conn->query("SELECT id,name FROM categories");
$suppliers = $conn->query("SELECT id,name FROM suppliers");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$name = $conn->real_escape_string($_POST['name']);
$desc = $conn->real_escape_string($_POST['description']);
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];
$cat = (int)$_POST['category_id'];
$sup = (int)$_POST['supplier_id'];

$conn->query("
INSERT INTO products(name,description,price,stock,category_id,supplier_id)
VALUES('$name','$desc',$price,$stock,$cat,$sup)
");

header("Location: index.php");
exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Product</title>

<style>
body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #ecd5e7; 
    margin: 0;
    padding: 40px 0;
    color: #334155;
}

.box {
    width: 100%;
    max-width: 460px;
    margin: 40px auto;
    background: white;
    padding: 32px;
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
    border: 1px solid #e2e8f0;
    box-sizing: border-box;
}

h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-top: 0;
    margin-bottom: 20px;
}

input, textarea, select {
    width: 100%;
    padding: 10px 14px;
    margin: 10px 0;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.95rem;
    color: #000000;
    background-color: #fff;
    box-sizing: border-box; 
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

textarea {
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
}

input:focus, textarea:focus, select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgb(79 70 229 / 0.1);
}

button {
    width: 100%;
    padding: 12px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 14px;
}

button:hover {
    background: #059669;
}
</style>
</head>

<body>

<div class="box">
<h2>Add Product</h2>

<form method="POST">

<input name="name" placeholder="Name" required>
<textarea name="description" placeholder="Description" required></textarea>
<input type="number" step="0.01" name="price" placeholder="Price" required>
<input type="number" name="stock" placeholder="Stock" required>

<select name="category_id" required>
<option value="">Category</option>
<?php while($c=$categories->fetch_assoc()): ?>
<option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
<?php endwhile; ?>
</select>

<select name="supplier_id" required>
<option value="">Supplier</option>
<?php while($s=$suppliers->fetch_assoc()): ?>
<option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
<?php endwhile; ?>
</select>

<button>Save</button>

</form>
</div>

</body>
</html>
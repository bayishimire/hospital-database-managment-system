<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: Only Staff, Doctor, and SuperAdmin
if (!in_array($_SESSION['role'], ['SuperAdmin', 'Staff', 'Doctor'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="card">
    <h2>💊 Pharmacy Inventory Control</h2>
    <p>Manage medicine stock levels and pricing accurately.</p>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <section class="card" style="box-shadow: none; border: 1px solid #eee;">
            <h3>Add New Medicine</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Medicine Name" required
                    style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ddd;">
                <textarea name="desc" placeholder="Description/Instruction"
                    style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ddd;"></textarea>
                <input type="number" name="stock" placeholder="Initial Stock" required
                    style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ddd;">
                <input type="number" step="0.01" name="price" placeholder="Price ($)" required
                    style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ddd;">
                <button type="submit" name="add_medicine"
                    style="width: 100%; padding: 12px; background: var(--accent); color: #fff; border: none; border-radius: 4px; cursor: pointer;">Add
                    to Inventory</button>
            </form>

            <?php
            if (isset($_POST['add_medicine'])) {
                $n = $_POST['name'];
                $d = $_POST['desc'];
                $s = $_POST['stock'];
                $p = $_POST['price'];
                $stmt = $conn->prepare("INSERT INTO medicines (name, description, stock, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssid", $n, $d, $s, $p);
                if ($stmt->execute())
                    echo "<p style='color: green;'>✅ Medicine added.</p>";
            }
            ?>
        </section>

        <section class="card" style="box-shadow: none; border: 1px solid #eee;">
            <h3>Stock Levels</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM medicines ORDER BY name ASC");
                    while ($row = $res->fetch_assoc()) {
                        $status = ($row['stock'] > 10) ? "<span style='color:green;'>In Stock</span>" : "<span style='color:red;'>Low Stock!</span>";
                        echo "<tr>";
                        echo "<td>#" . $row['medicine_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . $row['stock'] . "</td>";
                        echo "<td>$" . number_format($row['price'], 2) . "</td>";
                        echo "<td>" . $status . "</td>";
                        echo "<td><a href='?restock=" . $row['medicine_id'] . "' style='color: #0066cc;'>+ Restock</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<?php
if (isset($_GET['restock'])) {
    $id = (int) $_GET['restock'];
    $conn->query("UPDATE medicines SET stock = stock + 50 WHERE medicine_id = $id");
    echo "<script>window.location='medicines.php';</script>";
}
?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: SuperAdmin only for departments
if ($_SESSION['role'] != 'SuperAdmin') {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="card">
    <h2>🏥 Department Management</h2>
    <p>Organize hospital services and specialties.</p>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <section class="card" style="box-shadow: none; border: 1px solid #eee;">
            <h3>Add Department</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Department Name" required
                    style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ddd;">
                <textarea name="desc" placeholder="Description"
                    style="width: 100%; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ddd;"></textarea>
                <button type="submit" name="add_dept"
                    style="width: 100%; padding: 12px; background: var(--accent); color: #fff; border: none; border-radius: 4px; cursor: pointer;">Create
                    Department</button>
            </form>

            <?php
            if (isset($_POST['add_dept'])) {
                $n = $_POST['name'];
                $d = $_POST['desc'];
                $stmt = $conn->prepare("INSERT INTO departments (name_of_depart, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $n, $d);
                if ($stmt->execute())
                    echo "<p style='color: green;'>✅ Department created.</p>";
            }
            ?>
        </section>

        <section class="card" style="box-shadow: none; border: 1px solid #eee;">
            <h3>Existing Departments</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM departments");
                    while ($row = $res->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#" . $row['department_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['name_of_depart']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td><a href='?del=" . $row['department_id'] . "' style='color: #ff4d4d;'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<?php
if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    $conn->query("DELETE FROM departments WHERE department_id = $id");
    echo "<script>window.location='manage_departments.php';</script>";
}
?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
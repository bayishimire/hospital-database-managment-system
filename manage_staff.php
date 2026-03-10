<?php require_once __DIR__ . '/connection.php'; ?>
<?php
require_once __DIR__ . '/access_control.php';
restrict_access([]); // Restricted to SuperAdmin only
?>
<?php include 'header.php'; ?>

<div class="card">
    <h2>👔 Staff Administration</h2>
    <p>Manage non-medical hospital staff members.</p>

    <h3>Add New Staff Member</h3>
    <form method="POST" class="card" style="box-shadow: none; border: 1px solid #eee;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <input type="text" name="first_name" placeholder="First Name" required
                style="padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <input type="text" name="last_name" placeholder="Last Name" required
                style="padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <input type="text" name="role" placeholder="Role (e.g. Receptionist)" required
                style="padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <input type="text" name="phone" placeholder="Phone Number"
                style="padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <input type="email" name="email" placeholder="Email Address"
                style="padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <select name="department_id" style="padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
                <option value="">Select Department</option>
                <?php
                $depRes = $conn->query("SELECT * FROM departments");
                while ($d = $depRes->fetch_assoc()) {
                    echo "<option value='{$d['department_id']}'>{$d['name_of_depart']}</option>";
                }
                ?>
            </select>
            <button type="submit" name="add_staff"
                style="padding: 10px; background: var(--accent); color: #fff; border: none; border-radius: 4px; cursor: pointer; grid-column: span 3;">Register
                Staff</button>
        </div>
    </form>

    <?php
    if (isset($_POST['add_staff'])) {
        $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, role, phone, email, department_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $_POST['first_name'], $_POST['last_name'], $_POST['role'], $_POST['phone'], $_POST['email'], $_POST['department_id']);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Staff member added successfully.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
        }
    }
    ?>

    <h3>Staff Directory</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Department</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT s.*, d.name_of_depart FROM staff s LEFT JOIN departments d ON s.department_id = d.department_id ORDER BY s.staff_id DESC";
            $res = $conn->query($sql);
            while ($row = $res->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['staff_id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>" . ($row['name_of_depart'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['email'] ?? $row['phone']) . "</td>";
                echo "<td><a href='?del=" . $row['staff_id'] . "' onclick=\"return confirm('Are you sure?')\" style='color: #c00;'>Delete</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    $conn->query("DELETE FROM staff WHERE staff_id = $id");
    echo "<script>window.location='manage_staff.php';</script>";
}
?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
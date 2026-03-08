<?php require_once __DIR__ . '/connection.php'; ?>
<?php include 'header.php'; ?>

<div class="card">
    <?php
    $table = $_GET['tbl'] ?? '';
    // Basic sanitization: only allow alphanumeric and underscores
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

    // Verify table exists to prevent SQL injection
    $valid = false;
    $res = $conn->query('SHOW TABLES');
    if ($res) {
        while ($r = $res->fetch_array()) {
            if ($r[0] === $table) {
                $valid = true;
                break;
            }
        }
    }

    if (!$valid || empty($table)) {
        echo "<h2 style='color:#c00;'>❌ Invalid table name or table not found.</h2>";
        echo "<p><a href='overview.php'>Back to Overview</a></p>";
    } else {
        echo "<h2>Data for Table: " . htmlspecialchars($table) . "</h2>";

        // Pagination settings
        $perPage = 15;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $cntRes = $conn->query("SELECT COUNT(*) AS cnt FROM `$table`");
        $totalRows = $cntRes ? $cntRes->fetch_assoc()['cnt'] : 0;
        $totalPages = ceil($totalRows / $perPage);

        // Fetch paginated data
        $dataRes = $conn->query("SELECT * FROM `$table` LIMIT $perPage OFFSET $offset");
        if ($dataRes && $dataRes->num_rows > 0) {
            echo "<div style='overflow-x: auto;'>";
            echo "<table class='table'><thead><tr>";
            $fields = $dataRes->fetch_fields();
            foreach ($fields as $f)
                echo "<th>" . htmlspecialchars($f->name) . "</th>";
            echo "</tr></thead><tbody>";

            while ($row = $dataRes->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table></div>";

            // Pagination links
            if ($totalPages > 1) {
                echo "<div style='margin-top:1.5rem; text-align: center;'>";
                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeStyle = ($i == $page) ? "background: var(--primary); color: #fff; font-weight: bold;" : "background: #eee;";
                    echo "<a href='list_table.php?tbl={$table}&page={$i}' style='display:inline-block; padding: 0.5rem 1rem; margin:0 .2rem; text-decoration: none; border-radius: 4px; $activeStyle'>{$i}</a> ";
                }
                echo "</div>";
            }
        } else {
            echo "<p>No records found in this table.</p>";
        }
    }
    ?>
    <p style="margin-top: 2rem;"><a href="overview.php" style="color: var(--primary);">← Back to Overview</a></p>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
<?php
require_once __DIR__ . '/connection.php';
?>
<?php include 'header.php'; ?>
<div class="card">
    <?php
    // Get requested table name from query string
    $table = $_GET['tbl'] ?? '';
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table); // basic sanitisation
    
    // Verify that the table actually exists in the current database
    $validTables = [];
    $res = $conn->query('SHOW TABLES');
    while ($row = $res->fetch_array()) {
        $validTables[] = $row[0];
    }

    if (!in_array($table, $validTables)) {
        echo "<h2 style='color:#c00;'>❌ Invalid table name.</h2>";
    } else {
        echo "<h2>Table: {$table}</h2>";
        // Pagination parameters (optional, default 20 rows per page)
        $perPage = 20;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        // Get total rows for pagination links
        $cntRes = $conn->query("SELECT COUNT(*) AS cnt FROM `$table`");
        $totalRows = $cntRes->fetch_assoc()['cnt'];
        $totalPages = ceil($totalRows / $perPage);

        // Fetch rows for current page
        $dataRes = $conn->query("SELECT * FROM `$table` LIMIT $perPage OFFSET $offset");
        if ($dataRes && $dataRes->num_rows > 0) {
            echo "<table class='table'>";
            // Header row
            echo "<thead><tr>";
            $fields = $dataRes->fetch_fields();
            foreach ($fields as $field) {
                echo "<th>{$field->name}</th>";
            }
            echo "</tr></thead><tbody>";
            // Data rows
            $dataRes->data_seek(0); // reset pointer after fetch_fields
            while ($row = $dataRes->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $cell) {
                    // Escape HTML special chars for safety
                    $cell = htmlspecialchars($cell);
                    echo "<td>{$cell}</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";

            // Simple pagination controls
            if ($totalPages > 1) {
                echo "<div style='margin-top:1rem;'>";
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == $page) {
                        echo "<strong>{$i}</strong> ";
                    } else {
                        echo "<a href='list_table.php?tbl={$table}&page={$i}' style='margin:0 .3rem;'>{$i}</a> ";
                    }
                }
                echo "</div>";
            }
        } else {
            echo "<p>No records found in this table.</p>";
        }
    }
    ?>
</div>
<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
<?php
require_once __DIR__ . '/connection.php';
?>
<?php include 'header.php'; ?>
<div class="card">
    <h2>Database Overview</h2>
    <p>Below is a clear list of all tables in the current database with the number of rows in each.</p>
    <table class="table">
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Row Count</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Get list of tables
        $tablesResult = $conn->query('SHOW TABLES');
        while ($row = $tablesResult->fetch_array()) {
            $table = $row[0];
            $cntRes = $conn->query("SELECT COUNT(*) AS cnt FROM `$table`");
            $cnt = $cntRes->fetch_assoc()['cnt'];
            // Make table name a clickable link to view its rows
            echo "<tr><td><a href='list_table.php?tbl={$table}'>{$table}</a></td><td>{$cnt}</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
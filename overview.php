<?php require_once __DIR__ . '/connection.php'; ?>
<?php include 'header.php'; ?>

<div class="card">
    <h2>Database Overview</h2>
    <p>All tables in the hospital management database with row counts.</p>
    <table class="table">
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Row Count</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tablesResult = $conn->query('SHOW TABLES');
            if ($tablesResult) {
                while ($row = $tablesResult->fetch_array()) {
                    $table = $row[0];
                    $cntRes = $conn->query("SELECT COUNT(*) AS cnt FROM `$table`");
                    $cnt = $cntRes ? $cntRes->fetch_assoc()['cnt'] : "N/A";
                    echo "<tr><td><a href='list_table.php?tbl={$table}'>{$table}</a></td><td>{$cnt}</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Error listing tables.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
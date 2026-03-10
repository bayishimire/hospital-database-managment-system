<?php
require 'connection.php';
$res = $conn->query("DESC departments");
if ($res) {
    while ($r = $res->fetch_assoc())
        echo $r['Field'] . "\n";
} else {
    echo "NO_DEPTS";
}
?>
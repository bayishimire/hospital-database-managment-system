<?php
require 'connection.php';
$res = $conn->query("DESC doctors");
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . "\n";
}
?>
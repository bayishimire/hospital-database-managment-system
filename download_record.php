<?php
require_once __DIR__ . '/connection.php';

if (!isset($_GET['case_id'])) {
    die("Patient Case ID required.");
}

$cid = (int) $_GET['case_id'];

// Get Patient, Case, and Medical Record
$sql = "SELECT pc.*, p.*, mr.*, d.first_name as doc_fname, d.last_name as doc_lname, d.specialization
        FROM patient_cases pc
        JOIN patients p ON pc.patient_id = p.patient_id
        LEFT JOIN medicalrecords mr ON p.patient_id = mr.patient_id AND mr.record_date >= pc.created_at
        LEFT JOIN doctors d ON mr.doctor_id = d.doctor_id
        WHERE pc.case_id = $cid
        ORDER BY mr.record_id DESC LIMIT 1";

$res = $conn->query($sql);
$data = $res->fetch_assoc();

if (!$data) {
    die("Record not found.");
}

// Format the file
$filename = "Medical_Record_" . str_replace(' ', '_', $data['first_name']) . "_" . date('Ymd') . ".txt";

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "========================================================\n";
echo "        OFFICIAL MEDICAL CONSULTATION REPORT           \n";
echo "========================================================\n\n";

echo "DATE:       " . date('d M Y, H:i', strtotime($data['record_date'])) . "\n";
echo "CASE ID:    #" . $data['case_id'] . "\n\n";

echo "--- PATIENT IDENTITY ---\n";
echo "NAME:       " . $data['first_name'] . " " . $data['last_name'] . "\n";
echo "GENDER:     " . $data['gender'] . "\n";
echo "NATIONAL ID:" . $data['national_id'] . "\n";
echo "PHONE:      " . $data['phone'] . "\n";
echo "EMAIL:      " . ($data['email'] ?: 'N/A') . "\n";
echo "ADDRESS:    " . $data['address'] . "\n\n";

echo "--- CLINICAL INTAKE (THE FISH) ---\n";
echo "VITALS:     " . $data['vitals'] . "\n";
echo "COMPLAINT:  " . $data['chief_complaint'] . "\n\n";

echo "--- MEDICAL TEST RESULTS ---\n";
echo ($data['test_results'] ?: 'None recorded.') . "\n\n";

echo "--- OFFICIAL DIAGNOSIS ---\n";
echo $data['diagnosis'] . "\n\n";

echo "--- TREATMENT & PRESCRIPTION ---\n";
echo $data['treatment'] . "\n\n";

if ($data['operation_details']) {
    echo "--- OPERATION SUMMARY ---\n";
    echo $data['operation_details'] . "\n\n";
}

echo "--- PRACTITIONER ---\n";
echo "DOCTOR:     Dr. " . $data['doc_fname'] . " " . $data['doc_lname'] . "\n";
echo "SPECIALTY:  " . $data['specialization'] . "\n\n";

echo "========================================================\n";
echo "   RECOVER QUICKLY - CONFIDENTIAL MEDICAL DOCUMENT    \n";
echo "========================================================\n";

$conn->close();
?>
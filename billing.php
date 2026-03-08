<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: All logged in users can access, but data is filtered below.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
// Check if we are viewing a specific Payment Slip (Receipt)
$slipID = $_GET['print_slip'] ?? null;
if ($slipID) {
    // Specialized minimalist view for printing/previewing
    include 'header.php';
    // Include Signature Pad library and Google Fonts
    echo '<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">';

    $res = $conn->query("SELECT b.*, p.first_name, p.last_name, p.insurance, p.address 
                        FROM billing b 
                        JOIN patients p ON b.patient_id = p.patient_id 
                        WHERE b.bill_id = " . (int) $slipID);
    $bill = $res->fetch_assoc();
    if ($bill):
        ?>
        <div class="card" id="printableSlip"
            style="max-width: 650px; margin: 2rem auto; border: 2px solid #333; padding: 2.5rem; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative;">

            <div style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; color: #1e293b; font-size: 1.8rem; text-transform: uppercase; letter-spacing: 1px;">
                    Hospital Payment Slip</h2>
                <p style="margin: 5px 0; color: #64748b; font-weight: 500;">Official Receipt • Financial Services Department</p>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 2.5rem; line-height: 1.6;">
                <div>
                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Billed To:</strong><br>
                    <span
                        style="font-size: 1.1rem; font-weight: 600; color: #1e293b;"><?= $bill['first_name'] . " " . $bill['last_name'] ?></span><br>
                    <span style="color: #334155;"><?= $bill['address'] ?></span>
                </div>
                <div style="text-align: right;">
                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Receipt Details:</strong><br>
                    <strong>SLIP ID:</strong> #<?= str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT) ?><br>
                    <strong>BILL DATE:</strong> <?= date('M d, Y', strtotime($bill['billing_date'])) ?>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #333;">
                        <th style="text-align: left; padding: 12px; color: #475569;">Description of Service</th>
                        <th style="text-align: right; padding: 12px; color: #475569;">Amount ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 15px 12px; border-bottom: 1px solid #e2e8f0;">General Hospital Consultation &
                            Treatment</td>
                        <td style="text-align: right; padding: 15px 12px; border-bottom: 1px solid #e2e8f0; font-weight: 500;">
                            <?= number_format($bill['total_amount'], 2) ?></td>
                    </tr>
                    <tr style="background: #f1f5f9; font-weight: 700;">
                        <td style="padding: 12px; color: #1e293b;">TOTAL CHARGES</td>
                        <td style="text-align: right; padding: 12px; color: #2563eb; font-size: 1.2rem;">
                            $<?= number_format($bill['total_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; font-size: 0.9rem; color: #64748b;">INSURANCE COVERAGE: <span
                                style="color: #1e293b; font-weight: 600;"><?= $bill['insurance'] ?></span></td>
                        <td
                            style="text-align: right; padding: 12px; font-weight: 600; color: <?= ($bill['insurance'] != 'None') ? '#10b981' : '#f59e0b' ?>;">
                            <?= ($bill['insurance'] != 'None') ? "COVERED" : "OUT-OF-POCKET" ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px dashed #cbd5e1; padding-top: 2rem; margin-top: 1rem;">
                <div>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 500;">
                        Payment Status</p>
                    <div style="font-weight: 700; color: #1e293b; font-size: 1.1rem;"><?= strtoupper($bill['payment_status']) ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #94a3b8;">
                        Generated on: <?= date('Y-m-d H:i:s') ?>
                    </div>
                </div>
                <!-- Signature Area -->
                <div style="text-align: center; width: 250px;">
                    <div id="signContainer" style="position: relative; border: 2px solid #2563eb; border-radius: 8px; margin-bottom: 5px; background: #fff; min-height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <canvas id="signature-pad" width="250" height="100" style="touch-action: none; cursor: crosshair;"></canvas>
                        <div id="typedSign" style="display: none; font-family: 'Dancing Script', cursive; font-size: 2.2rem; white-space: nowrap; color: #000; padding: 0 10px;"></div>
                        <!-- Watermark -->
                        <div id="signHint" style="position: absolute; color: #e2e8f0; font-size: 1.5rem; font-weight: 700; pointer-events: none; text-transform: uppercase; letter-spacing: 2px; z-index: 0;">Sign Here</div>
                    </div>
                    <small style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px;">Authorized System Signature</small>
                </div>
            </div>

            <!-- Controls (Hidden in Print) -->
            <div id="uiControls" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; text-align: center;">
                <div style="margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px; display: flex; flex-direction: column; gap: 0.5rem; border: 1px solid #e2e8f0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569;">Ways to Sign:</label>
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button onclick="setSignMode('draw')" class="btn" style="background: #fff; border: 1px solid #cbd5e1; font-size: 0.8rem; color: #000;"><i class="fa-solid fa-pen-nib"></i> Hand Draw</button>
                        <button onclick="setSignMode('type')" class="btn" style="background: #fff; border: 1px solid #cbd5e1; font-size: 0.8rem; color: #000;"><i class="fa-solid fa-keyboard"></i> Type Name</button>
                    </div>
                    <div id="typeInputWrapper" style="display: none; margin-top: 0.5rem;">
                        <input type="text" id="signatureName" placeholder="Enter your full name..." oninput="updateTypedSign()" 
                            style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 2px solid #2563eb; outline: none; text-align: center;">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button id="clearBtn" onclick="clearSignature()" class="btn" style="background: #ef4444; color: white;">
                        <i class="fa-solid fa-trash-can"></i> Clear
                    </button>
                    <button onclick="handlePrint()" class="btn btn-primary" style="background: #10b981; padding: 0.75rem 2rem; font-size: 1rem;">
                        <i class="fa-solid fa-check-circle"></i> Confirm & Download Slip
                    </button>
                    <button onclick="window.history.back()" class="btn" style="background: #64748b; color: white;">
                        <i class="fa-solid fa-xmark"></i> Close
                    </button>
                </div>
            </div>
        </div>

        <script>
            let signMode = 'draw';
            const canvas = document.getElementById('signature-pad');
            const typedSign = document.getElementById('typedSign');
            const signHint = document.getElementById('signHint');
            const typeWrapper = document.getElementById('typeInputWrapper');
            const nameInput = document.getElementById('signatureName');
            
            // Initialize Signature Pad
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)'
            });

            signaturePad.onBegin = () => { signHint.style.opacity = '0'; };

            function setSignMode(mode) {
                signMode = mode;
                if (mode === 'draw') {
                    canvas.style.display = 'block';
                    typedSign.style.display = 'none';
                    typeWrapper.style.display = 'none';
                    signHint.style.display = 'block';
                    if (!signaturePad.isEmpty()) signHint.style.opacity = '0';
                } else {
                    canvas.style.display = 'none';
                    typedSign.style.display = 'block';
                    typeWrapper.style.display = 'block';
                    signHint.style.display = 'none';
                    nameInput.focus();
                }
            }

            function updateTypedSign() {
                typedSign.textContent = nameInput.value;
                if (nameInput.value.length > 20) {
                    typedSign.style.fontSize = '1.6rem';
                } else if (nameInput.value.length > 15) {
                    typedSign.style.fontSize = '1.8rem';
                } else {
                    typedSign.style.fontSize = '2.2rem';
                }
            }

            function clearSignature() {
                signaturePad.clear();
                nameInput.value = '';
                typedSign.textContent = '';
                signHint.style.opacity = '1';
                if (signMode === 'draw') signHint.style.display = 'block';
            }

            function handlePrint() {
                const isSigned = (signMode === 'draw' && !signaturePad.isEmpty()) || (signMode === 'type' && nameInput.value.trim() !== '');
                
                if (!isSigned) {
                    alert("Please provide a signature (draw or type) before downloading.");
                    return;
                }
                window.print();
            }

            // Adjust canvas resolution
            function resizeCanvas() {
                if (signMode !== 'draw') return;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
                signHint.style.opacity = '1';
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();
        </script>

        <style>
            @media print {

                #uiControls,
                nav,
                header,
                footer {
                    display: none !important;
                }

                body {
                    background: white !important;
                    margin: 0;
                    padding: 0;
                }

                .container--main {
                    margin: 0 !important;
                    padding: 0 !important;
                }

                #printableSlip {
                    box-shadow: none !important;
                    border: 2px solid #000 !important;
                    margin: 20px auto !important;
                }

                .card {
                    margin: 0 !important;
                }
            }

            #printableSlip * {
                font-family: 'Outfit', sans-serif;
            }
        </style>
        <?php
    endif;
    include 'footer.php';
    exit();
}
?>

<?php include 'header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-file-invoice-dollar"></i> Finance & Payment Services</h2>
        <div class="badge badge-success">
            Revenue Desk
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <?php if (in_array($_SESSION['role'], ['SuperAdmin', 'Staff', 'Service', 'Doctor'])): ?>
            <!-- Generate Bill Section -->
            <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
                <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-plus-circle"></i> Service Settlement
                </h3>
                <form method="POST">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 500;">Patient Records (Search ID or
                            Name)</label>
                        <select name="patient_id" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                            <?php
                            // Auto-select patient from Doctor Portal if redirected
                            $autoID = $_GET['patient_id'] ?? null;
                            $res = $conn->query("SELECT patient_id, first_name, last_name, insurance FROM patients");
                            while ($p = $res->fetch_assoc()) {
                                $sel = ($p['patient_id'] == $autoID) ? "selected" : "";
                                echo "<option value='{$p['patient_id']}' $sel>{$p['first_name']} {$p['last_name']} ({$p['insurance']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 500;">Total Operation Fee ($)</label>
                        <input type="number" step="0.01" name="amount" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <div style="margin-top: 1rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 500;">Settlement Type</label>
                        <select name="status"
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                            <option value="Pending">Direct Cash (Pending)</option>
                            <option value="Paid">Immediate Settlement</option>
                        </select>
                    </div>
                    <button type="submit" name="add_bill" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%;">
                        <i class="fa-solid fa-check-double"></i> Generate Service Bill
                    </button>
                </form>

                <?php
                if (isset($_POST['add_bill'])) {
                    $pid = $_POST['patient_id'];
                    $amt = $_POST['amount'];
                    $sts = $_POST['status'];
                    $date = date('Y-m-d H:i:s');
                    $stmt = $conn->prepare("INSERT INTO billing (patient_id, total_amount, payment_status, billing_date) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("idss", $pid, $amt, $sts, $date);
                    if ($stmt->execute())
                        echo "<p style='color: green;'>✅ Bill generated successfully.</p>";
                }
                ?>
            </section>
        <?php endif; ?>

        <!-- Ledger View -->
        <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
            <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-receipt"></i> Official Payment
                Ledger</h3>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $role = $_SESSION['role'];
                        $rel_id = $_SESSION['related_id'] ?? 0;
                        $sql = "SELECT b.*, p.first_name, p.last_name, p.insurance FROM billing b JOIN patients p ON b.patient_id = p.patient_id";

                        if ($role == 'Patient') {
                            $sql .= " WHERE b.patient_id = $rel_id";
                        }

                        $sql .= " ORDER BY b.billing_date DESC LIMIT 20";
                        $res = $conn->query($sql);
                        while ($row = $res->fetch_assoc()) {
                            $stsClass = ($row['payment_status'] == 'Paid') ? 'badge-success' : 'badge-pending';
                            echo "<tr>";
                            echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                            echo "<td><small>{$row['insurance']}</small></td>";
                            echo "<td><strong>$" . number_format($row['total_amount'], 2) . "</strong></td>";
                            echo "<td><span class='badge $stsClass'>{$row['payment_status']}</span></td>";
                            echo "<td>
                                    <a href='?print_slip={$row['bill_id']}' class='btn btn-primary' style='padding: 5px 10px; font-size: 0.75rem; background: #333;'>
                                        <i class='fa-solid fa-file-pdf'></i> Slip
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
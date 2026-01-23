<?php
include 'dbconnect.php';
session_start();
error_reporting(0);
$username = $_SESSION['username'];
$gnum = null;
$groupId = null; 

// Check if user has a gnum (is in a group)
$gnumQuery= "SELECT gnum from groups where roll=?";
$stmt = $conn->prepare($gnumQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$gnumResult = $stmt->get_result();
if ($gnumResult->num_rows > 0) {
    $gnum = $gnumResult->fetch_assoc()['gnum'];
}
$stmt->close();

// Check if gnum exists, and if it does, check if user has a group ID (has a project)
if ($gnum) {
    $numberQuery = "SELECT number,dAppDate FROM projinfo WHERE gnum = ?";
    $stmt = $conn->prepare($numberQuery);
    $stmt->bind_param("s", $gnum);
    $stmt->execute();
    $numberResult = $stmt->get_result();
    if ($numberResult->num_rows > 0) {
        $row = $numberResult->fetch_assoc();
        $groupId = $row['number'];
        $dAppDate = $row['dAppDate'];
    }
    $stmt->close();
}

$weeklyData = [];
// Fetch weekly data if Group ID exists
if ($groupId) {
    $weeklyDataQuery = "SELECT weeknum, summary, performance, dsub, deval FROM wanalysis WHERE number = ? ORDER BY weeknum ASC";
    $stmt = $conn->prepare($weeklyDataQuery);
    $stmt->bind_param("s", $groupId);
    $stmt->execute();
    $weeklyDataResult = $stmt->get_result();
    if ($weeklyDataResult->num_rows > 0) {
        $weeklyData = $weeklyDataResult->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// Handle JSON request for saving weekly summary
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $weekNumber = $input['weekNumber'] ?? null;
    $summary = $input['summary'] ?? '';

    if ($weekNumber && $summary && $groupId) {
        $updateQuery = "INSERT INTO wanalysis (number, weeknum, summary) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("iis", $groupId, $weekNumber, $summary);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            echo json_encode(['success' => true, 'message' => "Details saved for Week $weekNumber."]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Failed to save data.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAP - Weekly Analysis</title>
    <link href="https://unpkg.com/tailwindcss@^2.0/dist/tailwind.min.css" rel="stylesheet">
    <?php include 'favicon.php' ?>
    <style>
        /* Make sure the header and footer are not blurred */
        header, footer {
            z-index: 1001;
            position: relative;
        }

        .spinner-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">
    <?php include 'studentheaders.php' ?>
    <div class="flex-grow bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-4"><center>Weekly Analysis</center></h1>
        <hr class="my-8 border-black-300">

        <!-- Add spinner overlay -->
        <div id="spinner" class="spinner-overlay">
            <div class="spinner"></div>
        </div>

        <?php if (!$gnum): ?>
            <center>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">You are not assigned to any group.</span>
            </div>
            </center>
        <?php elseif (!$groupId): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <center>
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">Your group has no project records in our database.</span>
                </center>
            </div>

        <?php elseif (!$dAppDate): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <center>
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">No mentor has been allotted to your group.</span>
                </center>
            </div>
        <?php else: ?>
            <div id="weekly-forms">
                <!-- PHP to output existing weekly forms if they exist -->
                <?php foreach ($weeklyData as $week): ?>
                    <div class="week-section mb-6" data-week="<?php echo $week['weeknum']; ?>">
                        <h3 class="text-lg font-bold mb-2">Week <?php echo $week['weeknum']; ?></h3>
                        <textarea class="w-full p-2 border rounded mb-2 summary-field" rows="3" disabled><?php echo htmlspecialchars($week['summary']); ?></textarea>
                        <p class="text-gray-600"><b>Performance: </b></p>
                        <input type="text" class="w-full p-2 border rounded mb-2" value="<?php echo $week['performance']; ?>" disabled>
                        <p class="text-gray-600"><b>Submission Date: </b></p>
                        <input type="date" class="w-full p-2 border rounded mb-2" value="<?php echo $week['dsub']; ?>" disabled>
                        <p class="text-gray-600"><b>Evaluation Date: </b></p>
                        <input type="date" class="w-full p-2 border rounded mb-2" value="<?php echo $week['deval']; ?>" disabled>
                        <hr class="my-8 border-black-300">
                    </div>
                <?php endforeach; ?>

                <!-- New week form, either week 1 or next week if data exists -->
                <div class="week-section mb-6" data-week="<?php echo count($weeklyData) + 1; ?>">
                    <h3 class="text-lg font-bold mb-2">Week <?php echo count($weeklyData) + 1; ?></h3>
                    <textarea id="new-summary" class="w-full p-2 border rounded mb-2" rows="3" placeholder="Enter your summary here" maxlength="800"></textarea>
                    <button id="save-button" class="bg-blue-500 text-white py-2 px-4 rounded mt-4">Save</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php' ?>
    <script>
        // JavaScript to handle save button click and AJAX request
        document.getElementById("save-button").addEventListener("click", async function() {
            const summaryField = document.getElementById("new-summary");
            const summaryText = summaryField.value.trim();
            const weekNumber = document.querySelector(".week-section:last-of-type").getAttribute("data-week");
            const spinner = document.getElementById("spinner");
            const saveBtn = document.getElementById("save-button");

            if (summaryText === "") {
                alert("Please fill out the summary field before saving.");
                return;
            }

            const confirmSave = confirm("Are you sure you want to save this weekly summary?");
            if (confirmSave) {
                try {
                    // Show spinner and disable button
                    spinner.style.display = 'flex';
                    saveBtn.disabled = true;
                    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');

                    const response = await fetch("", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ summary: summaryText, weekNumber: weekNumber })
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error("Error:", error);
                    alert("An unexpected error occurred. Please try again later.");
                } finally {
                    // Hide spinner and enable button
                    spinner.style.display = 'none';
                    saveBtn.disabled = false;
                    saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        });
    </script>
</body>
</html>

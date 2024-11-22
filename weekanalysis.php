<?php
include 'dbconnect.php';
session_start();
$username = $_SESSION['username'];
$gnum = null;
$groupId = null; 

// Check if user has a gnum (is in a group)
$gnumQuery= "SELECT gnum from groups where roll='$username' LIMIT 1";
$gnumResult= $conn->query($gnumQuery);
if ($gnumResult->num_rows > 0) {
    $gnum = $gnumResult->fetch_assoc()['gnum'];
}

// Check if gnum exists, and if it does, fetch Group ID (number) from projinfo
if ($gnum) {
    $numberQuery = "SELECT number FROM projinfo WHERE gnum = '$gnum' LIMIT 1";
    $numberResult = $conn->query($numberQuery);
    if ($numberResult->num_rows > 0) {
        $groupId = $numberResult->fetch_assoc()['number'];
    }
}

$weeklyData = [];
// Fetch weekly data if Group ID exists
if ($groupId) {
    $weeklyDataQuery = "SELECT weeknum, summary, performance, dsub, deval FROM wanalysis WHERE number = '$groupId' ORDER BY weeknum ASC";
    $weeklyDataResult = $conn->query($weeklyDataQuery);
    if ($weeklyDataResult->num_rows > 0) {
        $weeklyData = $weeklyDataResult->fetch_all(MYSQLI_ASSOC);//Fetches all rows as an associative array at once and stores in $weeklyData array
        // while ($row = $weeklyDataResult->fetch_assoc()) {//Alternatively, you can also use this while loop to fetch each row one by one and then store it in an array
        //     $weeklyData[] = $row;
        // }
    }
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
            echo json_encode(['success' => 'true', 'message' => "Details saved for Week $weekNumber."]);
            exit;
        }
    }
    echo json_encode(['success' => 'false', 'message' => 'Failed to save data.']);
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
    </style>
</head>
<body class="bg-white text-gray-800 flex flex-col min-h-screen">
    <?php include 'studentheaders.php' ?>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Weekly Analysis</h2>

        <?php if (!$gnum): ?>
            <p class="text-red-500">You are not assigned to any group.</p>
        <?php elseif (!$groupId): ?>
            <p class="text-red-500">Your group has no project records in our database.</p>
        <?php else: ?>
            <div id="weekly-forms">
                <!-- PHP to output existing weekly forms if they exist -->
                <?php foreach ($weeklyData as $week): ?>
                    <div class="week-section mb-6" data-week="<?php echo $week['weeknum']; ?>">
                        <h3 class="text-lg font-bold mb-2">Week <?php echo $week['weeknum']; ?></h3>
                        <textarea class="w-full p-2 border rounded mb-2 summary-field" rows="3" disabled><?php echo htmlspecialchars($week['summary']); ?></textarea>
                        <p class="text-gray-600">Performance: <?php echo $week['performance']; ?></p>
                        <p class="text-gray-600">Date of Submission: <?php echo $week['dsub']; ?></p>
                        <p class="text-gray-600">Date of Evaluation: <?php echo $week['deval']; ?></p>
                    </div>
                <?php endforeach; ?>

                <!-- New week form, either week 1 or next week if data exists -->
                <div class="week-section mb-6" data-week="<?php echo count($weeklyData) + 1; ?>">
                    <h3 class="text-lg font-bold mb-2">Week <?php echo count($weeklyData) + 1; ?></h3>
                    <textarea id="new-summary" class="w-full p-2 border rounded mb-2" rows="3" placeholder="Enter your summary here"></textarea>
                    <button id="save-button" class="bg-blue-500 text-white py-2 px-4 rounded mt-4">Save</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php' ?>
    <script>
        // JavaScript to handle save button click and AJAX request
        document.getElementById("save-button").addEventListener("click", function() {
            const summaryField = document.getElementById("new-summary");
            const summaryText = summaryField.value.trim();
            const weekNumber = document.querySelector(".week-section:last-of-type").getAttribute("data-week");

            if (summaryText === "") {
                alert("Please fill out the summary field before saving.");
                return;
            }

            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ summary: summaryText, weekNumber: weekNumber })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Reload to display the new week data
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        });
    </script>

    
</body>
</html>

<?php
include 'dbconnect.php';
session_start();
$username = $_SESSION['username'];
$gnum = null;
$groupId = null; 

// Check if user has a gnum (is in a group)
if (isset($_SESSION['gnum'])) {
    $gnum = $_SESSION['gnum'];
}

// Check if gnum exists, and if it does, fetch Group ID (number) from projinfo
if ($gnum) {
    $numberQuery = "SELECT number FROM projinfo WHERE gnum = '$gnum' LIMIT 1";
    $numberResult = $conn->query($numberQuery);
    if ($numberResult->num_rows > 0) {
        $groupId = $numberResult->fetch_assoc()['number'];
    }
}

// Fetch weekly data if Group ID exists
$weeklyData = [];
if ($groupId) {
    $weeklyDataQuery = "SELECT number, summary, performance, dsub, deval FROM wanalysis WHERE number = '$groupId'";
    $weeklyDataResult = $conn->query($weeklyDataQuery);
    if ($weeklyDataResult->num_rows > 0) {
        $weeklyData = $weeklyDataResult->fetch_all(MYSQLI_ASSOC);//Fetches all rows as an associative array at once and stores in $weeklyData array
    }
}

// Process form submission to save summary if POST request is detected
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['summary']) && isset($_POST['weekNumber'])) {
    $summary = $_POST['summary'];
    $weekNumber = $_POST['weekNumber'];

    // Update the specific week's summary in the database
    $updateQuery = "UPDATE wanalysis SET summary = ? WHERE number = ? AND number = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sii", $summary, $groupId, $weekNumber);
    
    if ($stmt->execute()) {
        $statusMessage = "Summary saved successfully for Week $weekNumber.";
    } else {
        $statusMessage = "Error saving summary: " . $stmt->error;
    }
    $stmt->close();

    // Reload the weekly data to reflect the update
    $weeklyData = [];
    $stmt = $conn->prepare($weeklyDataQuery);
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $weeklyData[] = $row;
    }
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
<body class="bg-gray-100 p-6">
<?php include 'studentheaders.php' ?>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Weekly Analysis</h2>

        <?php if (!$gnum): ?>
            <p class="text-red-500">You are not assigned to any group.</p>
        <?php elseif (!$groupId): ?>
            <p class="text-red-500">Your group has no records in the project information table.</p>
        <?php else: ?>
            <?php if (isset($statusMessage)) echo "<p class='text-green-500'>$statusMessage</p>"; ?>
            
            <?php foreach ($weeklyData as $week): ?>
                <form method="POST" action="">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-2">Week <?php echo $week['number']; ?></h3>
                        
                        <!-- Weekly Summary Field (Editable) -->
                        <label class="block mb-2">Weekly Summary:</label>
                        <textarea name="summary" 
                                  class="w-full p-2 border rounded mb-2" rows="3" 
                                  placeholder="Enter your summary here"
                                  oninput="toggleSaveButton(this)">
                                  <?php echo htmlspecialchars($week['summary']); ?></textarea>
                        
                        <!-- Hidden fields to send the week number and group ID to the server -->
                        <input type="hidden" name="weekNumber" value="<?php echo $week['number']; ?>">

                        <!-- Performance Field (Disabled) -->
                        <label class="block mb-2">Performance:</label>
                        <select class="w-full p-2 border rounded mb-2" disabled>
                            <option value="satisfactory" <?php if ($week['performance'] === 'satisfactory') echo 'selected'; ?>>Satisfactory</option>
                            <option value="not_satisfactory" <?php if ($week['performance'] === 'not_satisfactory') echo 'selected'; ?>>Not Satisfactory</option>
                        </select>

                        <!-- Date of Submission Field (Disabled) -->
                        <label class="block mb-2">Date of Submission:</label>
                        <input type="date" class="w-full p-2 border rounded mb-2" value="<?php echo $week['dsub']; ?>" disabled>

                        <!-- Date of Evaluation Field (Disabled) -->
                        <label class="block mb-2">Date of Evaluation:</label>
                        <input type="date" class="w-full p-2 border rounded mb-2" value="<?php echo $week['deval']; ?>" disabled>
                        
                        <!-- Save Button -->
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded mt-4">Save</button>
                    </div>
                </form>
                <hr class="my-4">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php include 'footer.php' ?>
</body>
</html>

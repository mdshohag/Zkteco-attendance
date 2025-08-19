<?php
	

 $servername = "localhost"; 
 $username = "root"; 
 $password = "12345"; 
 $database = "db_attendance"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully";
}
	


	$employeeId = $_GET['employeeId'];
	$eventTime = $_GET['eventTime'];
	$isCheckin = $_GET['isCheckin'];
	$downloadDate = $_GET['downloadDate'];



if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Define minimum time gap (in minutes)
$min_time_gap = 5;

// Extract date part from eventTime for today's date
$todayDate = date('Y-m-d', strtotime($eventTime));

// Check if there is already a Check-in record for today
$check_attendance = "SELECT id, eventTime, isCheckin FROM attendance 
                     WHERE employeeId = '$employeeId' AND DATE(eventTime) = '$todayDate' AND isCheckin = '1' 
                     ORDER BY eventTime ASC";
$result = $conn->query($check_attendance);

if ($result->num_rows == 0) {
    // No check-in for today → Insert as first punch (Check-in)
    $insert_query = "INSERT INTO attendance (employeeId, eventTime, isCheckin, downloadDate) 
                     VALUES ('$employeeId', '$eventTime', 1, '$downloadDate')";
    $conn->query($insert_query);
    echo "<script>alert('First Check-in recorded');</script>";
} else {
    // Check if there is already a Check-out record for today
    $check_attendance_out = "SELECT id, eventTime FROM attendance 
                             WHERE employeeId = '$employeeId' AND DATE(eventTime) = '$todayDate' AND isCheckin = '0' 
                             ORDER BY eventTime ASC";
    $result_out = $conn->query($check_attendance_out);

    $last_record = $result->fetch_assoc();

    $last_event_time = strtotime($last_record['eventTime']); // Convert to timestamp
    $new_event_time = strtotime($eventTime);

        // Check if the new event is within the minimum time gap
        if (($new_event_time - $last_event_time) < ($min_time_gap * 60)) {
            echo "<script>alert('Minimum time gap of $min_time_gap minutes not met. Entry ignored.');</script>";
            exit();
        } else {

		    if ($result_out->num_rows == 0) {

		        // No check-out recorded yet → Insert as check-out (last punch)
		        $insert_check_out = "INSERT INTO attendance (employeeId, eventTime, isCheckin, downloadDate) 
		                             VALUES ('$employeeId', '$eventTime', 0, '$downloadDate')";
		        $conn->query($insert_check_out);
		        echo "<script>alert('Second punch recorded as Check-out');</script>";

		    } else {
		        // If a check-out already exists → Update it to the latest check-out time
		        $record = $result_out->fetch_assoc();

		        $update_query = "UPDATE attendance SET eventTime = '$eventTime', downloadDate = '$downloadDate'
		                         WHERE id = " . $record['id'];
		        $conn->query($update_query);
		        echo "<script>alert('Last Check-out updated');</script>";
		    }

		 }


}

$conn->close();


?>


<?php

$timezone = "Asia/Dhaka";
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set($timezone);
}

require 'zklibrary.php';

// List of machine IPs
$device_ips = ['172.16.14.103'];

foreach ($device_ips as $ip) {
    try {
        echo "<h2>Connecting to Device: $ip</h2>";
        $zk = new ZKLibrary($ip, 4370);
        $zk->connect();
        $zk->disableDevice();

        // Fetch User Data
        // $users = $zk->getUser();

        // echo "<h3>Users from $ip</h3>";

        ?>

       <!--  <table border="1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>UID</th>
                    <th>ID-Card</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                <//?php
                $no = 0;
                foreach ($users as $key => $user) {
                    $no++;
                    echo "<tr>
                        <td align='right'>$no</td>
                        <td>{$key}</td>
                        <td>{$user[0]}</td>
                        <td>{$user[1]}</td>
                        <td>{$user[2]}</td>
                        <td>{$user[3]}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table> -->

        <!-- Fetch Attendance Data -->
        <h3>Attendance from <?php echo $ip; ?></h3>
        <?php
        $attendance = $zk->getAttendance();

        ?>
        <table border="1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>UID</th>
                    <th>ID</th>
                    <th>ID-Card</th>
                    <th>isCheckin</th>
                    <th>eventTime</th>
                    <th>downloadDate</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $n = 0;
                $from = new DateTimeZone('GMT'); 
                $to = new DateTimeZone('Asia/Dhaka');

                $currDate = new DateTime('now', $to);
                $todayDate = $currDate->format('Y-m-d');

                foreach ($attendance as $keys => $attendancedata) {
                    $eventTime = $attendancedata[3];

                    $eventUTCDate = (new DateTime($eventTime, $from))->format('Y-m-d');

                     // $tor = "2025-03-16";

                    if ($eventUTCDate === $todayDate) {
                        $downloadDate = $currDate->format('Y-m-d H:i:s');
                        $n++;
                        echo "<tr>
                            <td align='right'>$n</td>
                            <td>{$keys}</td>
                            <td>{$attendancedata[0]}</td>
                            <td>{$attendancedata[1]}</td>
                            <td>{$attendancedata[2]}</td>
                            <td>{$eventTime}</td>
                            <td>{$downloadDate}</td>
                        </tr>";

                        // Send Data to API
                        $atten_sender = "lo?" . http_build_query([
                            'eventTime' => $eventTime,
                            'downloadDate' => $downloadDate,
                            'employeeId' => $attendancedata[1],
                            'isCheckin' => $attendancedata[2]
                        ]);
                        
                        curl_call($atten_sender);
                    }
                }
                ?>
            </tbody>
        </table>
        <?php

        // Enable & Disconnect
        $zk->enableDevice();
        // $zk->disconnect();
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error connecting to device at IP: $ip - " . $e->getMessage() . "</p>";
    }
}

// Function to Call API
function curl_call($url)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => ''
    ));

    $output = curl_exec($curl);
    curl_close($curl);

    return $output;
}
?>

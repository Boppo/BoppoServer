<?php

	// 1 - Establish database connection
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Older/getFriendStatus.php';

	// 2 - Decode incoming Json contents
	$_POST = json_decode(file_get_contents("php://input"), true);

	// 3 - Save the user string to a variable
	$searched_by_uid = $_POST["searchedByUid"];
	$searched_user = $_POST["searchedUser"];
	//$searched_username = "asdas";

	// 4 - Split the user string into words, initialize, and return array
	$names = explode(" ", $searched_user);
	for($i = 0; $i < count($names); $i++) {
		$names[$i] = "%".$names[$i]."%";
	}
	$data = array();
	//$names[0] = "%Damian%";

	// 5.A - IF THE STRING CONSISTS OF ONE WORD...
	if (!(count($names) == 1 && $names[0] == "%%")) {

        if (count($names) == 1) {
            // echo "|" . $names[0] . "|";
            // 5.1 - PREPARE THE QUERY
            $query = "SELECT uid, username, first_name, last_name
                      FROM T_USER
                      WHERE 
                        username LIKE ? OR 
                        first_name LIKE ? OR 
                        last_name LIKE ?";
            $statement = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($statement, "sss", $names[0], $names[0], $names[0]);
        }
        // 5.B - IF THE STRING CONSISTS OF TWO WORDS...
        else if (count($names) == 2) {
            // 5.1 - PREPARE THE QUERY
            $query = "SELECT uid, username, first_name, last_name
                      FROM T_USER
                      WHERE 
                        (first_name LIKE ? AND last_name LIKE ?) OR
                        (last_name LIKE ? AND first_name LIKE ?)";
            $statement = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($statement, "ssss",
                $names[0], $names[1], $names[0], $names[1]);
        }
        // 5.C - IF THE STRING CONSISTS OF MORE THAN TWO WORDS...
        else if (count($names) > 2) {
            // 5.1 - PREPARE THE QUERY
            $query = "SELECT uid, username, first_name, last_name
                      FROM T_USER
                      WHERE 
                        (first_name LIKE ? AND last_name LIKE ?) OR
                        (last_name LIKE ? AND first_name LIKE ?)";
            $statement = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($statement, "ssss",
                $names[0], $names[2], $names[0], $names[2]);
        }

        // 5.2 - EXECUTE THE QUERY
        mysqli_stmt_execute($statement);

        // 6 - STORE THE QUERY RESUlT IN VARIABES
        mysqli_stmt_bind_result($statement, $uid, $username, $first_name, $last_name);
        while(mysqli_stmt_fetch($statement)) {
        	// 7.1 - FETCH THE FRIENDSHIP STATUS BETWEEN THE TWO USERS
        	$friendship_status = fetchFriendshipStatus($searched_by_uid, $uid);
            // 7.2 - STORE THE RESULTING VARIABLES IN ASSOCIATIVE ARRAY
            $result = array(
                "uid" => $uid,
                "username" => $username,
                "firstName" => $first_name,
                "lastName" => $last_name,
            	"friendshipStatus" => $friendship_status
            );
            array_push($data, $result);
        }

        // 9 - RETURN JSON-ENCODED ARRAY AND CLOSE STATEMENT
        echo json_encode($data);

        mysqli_stmt_close($statement);
	}
	else {
		echo "INCORRECT STRING.";
	}
?>
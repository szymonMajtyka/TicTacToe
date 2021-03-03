<?php
	include_once(__DIR__ . '/database.php');
	$anInactiveUserDuration = 5;
	$anInactiveUserTime = time() - $anInactiveUserDuration;
	$astrQuery = 'UPDATE Game SET UserA=NULL WHERE UserA IS NOT NULL AND LastActiveA<' . $anInactiveUserTime;
	$aResult = sendQuery();
	$astrQuery = 'UPDATE Game SET UserB=NULL WHERE UserB IS NOT NULL AND LastActiveB<' . $anInactiveUserTime;
	$aResult = sendQuery();
	$astrQuery = 'SELECT COUNT(*) AS IsUser FROM game WHERE UserA IS NOT NULL LIMIT 1';
	$aResult = sendQuery();
	$abIsUserNotNullA = FALSE;
	$aRow = $aResult->fetch_assoc();
	if (isset($aRow)) {
		$anCountUsers = $aRow['IsUser'];
		if ($anCountUsers > 0) {
			$abIsUserNotNullA = TRUE;
		}
	}
	$astrQuery = 'SELECT COUNT(*) AS IsUser FROM game WHERE UserB IS NOT NULL LIMIT 1';
	$aResult = sendQuery();
	$abIsUserNotNullB = FALSE;
	$aRow = $aResult->fetch_assoc();
	if (isset($aRow)) {
		$anCountUsers = $aRow['IsUser'];
		if ($anCountUsers > 0) {
			$abIsUserNotNullB = TRUE;
		}
	}
	$abWeAreUserA = FALSE;
	if ($abIsUserNotNullA) {
		$astrQuery = 'SELECT COUNT(*) AS CountUsers FROM game WHERE UserA="' . $g_SessionSid . '"LIMIT 1';
		$aResult = sendQuery();
		$aRow = $aResult->fetch_assoc();
		if (isset($aRow)) {
			$anCountUsers = $aRow['CountUsers'];
			if ($anCountUsers > 0) {
				$abWeAreUserA = TRUE;
				$astrQuery = 'UPDATE game SET LastActiveA="' . time() . '"';
				$aResult = sendQuery();
			}
		}
	}
	$abWeAreUserB = FALSE;
	if ((!$abWeAreUserA) && ($abIsUserNotNullB)) {
		$astrQuery = 'SELECT COUNT(*) AS CountUsers FROM game WHERE UserB="' . $g_SessionSid . '"LIMIT 1';
		$aResult = sendQuery();
		$aRow = $aResult->fetch_assoc();
		if (isset($aRow)) {
			$anCountUsers = $aRow['CountUsers'];
			if ($anCountUsers > 0) {
				$abWeAreUserB = TRUE;
				$astrQuery = 'UPDATE game SET LastActiveB="' . time() . '"';
				$aResult = sendQuery();
			}
		}
	}
	$abResetBoard = FALSE;
	if ((!$abWeAreUserA) && (!$abWeAreUserB)) {
		if (!$abIsUserNotNullA) {
			$astrQuery = 'UPDATE game SET UserA="' . $g_SessionSid . '",LastActiveA="' . time() . '"';
			$aResult = sendQuery();
			$abIsUserNotNullA = TRUE;
			$abWeAreUserA = TRUE;
			$abResetBoard = TRUE;
		} else if (!$abIsUserNotNullB) {
			$astrQuery = 'UPDATE game SET UserB="' . $g_SessionSid . '",LastActiveB="' . time() . '"';
			$aResult = sendQuery();
			$abIsUserNotNullB = TRUE;
			$abWeAreUserB = TRUE;
			$abResetBoard = TRUE;
		} else {
		}
	}
	if ($abResetBoard) {
		$astrQuery = 'UPDATE game SET Board="0,0,0,0,0,0,0,0,0",RoundUser=FLOOR(0 + RAND()*2 )';
		$aResult = sendQuery();
	}
	$astrBoard = '';
	$astrRoundUser = '';
	$abWinnerUserA = FALSE;
	$abWinnerUserB = FALSE;
	if ($abIsUserNotNullA && $abIsUserNotNullB) {
		$astrQuery = 'SELECT * FROM game LIMIT 1';
		$aResult = sendQuery();
		$aRow = $aResult->fetch_assoc();
		if (isset($aRow)) {
			$astrBoard = $aRow['Board'];
			$astrRoundUser = $aRow['RoundUser'];
			$avBoard = explode(",", $astrBoard);
			if (9 == count($avBoard)) {
				if (isset($_REQUEST['CellIndex_Click'])) {
					$anCellIndex_Click = (int) $_REQUEST['CellIndex_Click'];
					$abChangeTurn = FALSE;
					if (('0' == $astrRoundUser) && ($abWeAreUserA)) {
						if ('0' == $avBoard[$anCellIndex_Click]) {
							$avBoard[$anCellIndex_Click] = '1';
							$abChangeTurn = TRUE;
						}
					} else if (('1' == $astrRoundUser) && ($abWeAreUserB)) {
						if ('0' == $avBoard[$anCellIndex_Click]) {
							$avBoard[$anCellIndex_Click] = '2';
							$abChangeTurn = TRUE;
						}
					}
					if ($abChangeTurn) {
						$astrBoard = implode(",", $avBoard);
						$astrRoundUser = ('0' == $astrRoundUser) ? '1' : '0';
						$astrQuery = 'UPDATE game SET Board="' . $astrBoard . '",RoundUser=' . $astrRoundUser;
						$aResult = sendQuery();
					}
				}
				$avvCheck = array(
					array(0, 1, 2)
					, array(3, 4, 5)
					, array(6, 7, 8)
					, array(0, 3, 6)
					, array(1, 4, 7)
					, array(2, 5, 8)
					, array(0, 4, 8)
					, array(6, 4, 2)
				);
				foreach ($avvCheck as $avCheck) {
					if (('1' == $avBoard[$avCheck[0]]) && ('1' == $avBoard[$avCheck[1]]) && ('1' == $avBoard[$avCheck[2]])) {
						$abWinnerUserA = TRUE;
					} else if (('2' == $avBoard[$avCheck[0]]) && ('2' == $avBoard[$avCheck[1]]) && ('2' == $avBoard[$avCheck[2]])) {
						$abWinnerUserB = TRUE;
					}
				}
			}
		}
	}
	include_once(__DIR__ . '/database_close.php');
	$anNumUsers = ($abIsUserNotNullA ? 1 : 0) + ($abIsUserNotNullB ? 1 : 0);
	$aResponse = array();
	$aResponse["NumUsers"] = $anNumUsers;
	$aResponse["User"] = ($abWeAreUserA ? 'UserA' : ($abWeAreUserB ? 'UserB' : 'Full'));
	$aResponse["Board"] = $astrBoard;
	$aResponse["RoundUser"] = $astrRoundUser;
	$aResponse["Winner"] = ($abWinnerUserA ? 'UserA' : ($abWinnerUserB ? 'UserB' : ''));
	echo json_encode($aResponse);
?>
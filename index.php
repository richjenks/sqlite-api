<?php

/**
 * SQLite API
 *
 * Interact with remote SQLite databases
 * Database filename in querystring (e.g. http://localhost:8000?database.sqlite3)
 * Write SQL statement in request body
 * Response is always JSON and appropriate for the request
 */

/**
 * Sends a JSON response with optional HTTP Status
 *
 * @param mixed $data Data to be served as HTTP response body
 * @param int   $status (Optional) HTTP response code
 */
function respond($data, $status = 200) {
	header('Content-Type: application/json');
	http_response_code($status);
	echo json_encode($data, JSON_FORCE_OBJECT);
	die;
}

// Check database is specified and exists
$database = str_replace(['\\', '/'], '', $_SERVER['QUERY_STRING']);
if (empty($database)) respond(['error' => 'Specify database name in querystring'], 400);
if (!file_exists(__DIR__ . '/' . $database)) respond(['error' => 'Database not found'], 404);

// If ok, select database
$db = str_replace(['/', '\\'], '', $database);
$db = new PDO('sqlite:' . $db);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get statement from request body
$sql = trim(file_get_contents('php://input'));
if (empty($sql)) respond(['error' => 'Provide query in request body'], 400);
$method = strtoupper(explode(' ', $sql)[0]);

// Select or execute
try {
	switch ($method) {
		case 'SELECT':
			$query = $db->prepare($sql);
			$query->execute();
			respond($query->fetchAll(PDO::FETCH_ASSOC));
			break;

		case 'INSERT':
			$rows = $db->exec($sql);
			respond(['rows' => $rows], 201);
			break;

		default:
			$rows = $db->exec($sql);
			respond(['rows' => $rows]);
			break;
	}
}

catch (PDOException $e) { respond(['error' => $db->errorInfo()[2]], 400); }

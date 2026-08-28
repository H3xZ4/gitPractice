<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ── List / Search ────────────────────────────────────────────────────────
    case 'list':
        $searchQuery = trim($_GET['search_query'] ?? '');
        $searchField = $_GET['search_field'] ?? 'name';

        $whereClause = '';
        $params      = [];
        $paramTypes  = '';

        if ($searchQuery !== '') {
            if ($searchField === 'id') {
                $whereClause = 'WHERE ID = ?';
                $paramTypes  = 'i';
                $params[]    = intval($searchQuery);
            } else {
                $whereClause = 'WHERE name LIKE ? OR email LIKE ?';
                $searchTerm  = '%' . $searchQuery . '%';
                $paramTypes  = 'ss';
                $params[]    = $searchTerm;
                $params[]    = $searchTerm;
            }
        }

        $sql  = 'SELECT ID, name, email FROM users ' . $whereClause . ' ORDER BY ID DESC LIMIT 200';
        $stmt = $conn->prepare($sql);
        if ($whereClause !== '') {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $users  = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['success' => true, 'users' => $users]);
        break;

    // ── Get single user (for edit form) ─────────────────────────────────────
    case 'get':
        $id   = intval($_GET['id'] ?? 0);
        $sql  = 'SELECT ID, name, email FROM users WHERE ID = ? LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
        break;

    // ── Add ──────────────────────────────────────────────────────────────────
    case 'add':
        $name     = trim($_POST['name']  ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password']   ?? '';

        if ($name === '' || $email === '') {
            echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
            break;
        }

        // Check duplicate email
        $check = $conn->prepare('SELECT ID FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists. Use a different email or edit the user.']);
            $check->close();
            break;
        }
        $check->close();

        $hashedPassword = $password !== ''
            ? password_hash($password, PASSWORD_DEFAULT)
            : password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'User added successfully.',
                'user'    => ['ID' => $newId, 'name' => $name, 'email' => $email],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not add user. Please try again.']);
        }
        $stmt->close();
        break;

    // ── Edit ─────────────────────────────────────────────────────────────────
    case 'edit':
        $id       = intval($_POST['user_id'] ?? 0);
        $name     = trim($_POST['name']      ?? '');
        $email    = trim($_POST['email']     ?? '');
        $password = $_POST['password']       ?? '';

        if ($id <= 0 || $name === '' || $email === '') {
            echo json_encode(['success' => false, 'message' => 'Name and email are required to update a user.']);
            break;
        }

        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE ID = ?');
            $stmt->bind_param('sssi', $name, $email, $hashedPassword, $id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE ID = ?');
            $stmt->bind_param('ssi', $name, $email, $id);
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully.',
                'user'    => ['ID' => $id, 'name' => $name, 'email' => $email],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not update user. Please try again.']);
        }
        $stmt->close();
        break;

    // ── Delete ───────────────────────────────────────────────────────────────
    case 'delete':
        $id   = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM users WHERE ID = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not delete user. Please try again.']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
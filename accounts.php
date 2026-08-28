<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Management</title>
    <!-- jQuery from CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            background: #f0f4ff;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: #102a43;
            color: #cbd5e1;
            display: flex;
            flex-direction: column;
            padding: 28px 20px;
            flex-shrink: 0;
        }
        .sidebar-brand h4 {
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }
        .sidebar-brand small { font-size: 11px; color: #64748b; }
        .sidebar nav {
            margin-top: 36px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sidebar nav a {
            display: block;
            padding: 9px 14px;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13.5px;
            transition: background .15s, color .15s;
        }
        .sidebar nav a:hover,
        .sidebar nav a.active { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar nav a.danger { color: #f87171; }
        .sidebar nav a.danger:hover { background: rgba(248,113,113,.12); color: #fca5a5; }

        /* ── Main ── */
        .main { flex: 1; padding: 28px; min-width: 0; }

        /* ── App Header ── */
        .app-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: #fff;
            border-radius: 14px;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .app-header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .app-header p  { font-size: 13px; color: rgba(255,255,255,.65); }
        .app-header .user-badge { text-align: right; }
        .app-header .user-badge span  { font-size: 12px; color: rgba(255,255,255,.55); display: block; margin-bottom: 2px; }
        .app-header .user-badge strong { font-size: 14px; }

        /* ── Alerts ── */
        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeIn .25s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-warning { background: #fef9c3; color: #713f12; border: 1px solid #fde68a; }
        .alert-close {
            background: none; border: none; cursor: pointer;
            font-size: 18px; line-height: 1; color: inherit; opacity: .6;
        }
        .alert-close:hover { opacity: 1; }

        /* ── Grid ── */
        .grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(16,42,67,.08);
            padding: 24px;
        }
        .card-title { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .card-sub   { font-size: 12px; color: #64748b; margin-bottom: 20px; }

        /* ── Form ── */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px; }
        .form-group input {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 13.5px; color: #1e293b;
            transition: border-color .2s;
            outline: none;
        }
        .form-group input:focus { border-color: #2563eb; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600;
            border: none; cursor: pointer; text-decoration: none; transition: opacity .15s, background .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-primary { background: #2563eb; color: #fff; width: 100%; padding: 10px; }
        .btn-outline { background: transparent; border: 1.5px solid #cbd5e1; color: #475569; font-size: 12px; padding: 5px 12px; }
        .btn-outline:hover { border-color: #94a3b8; }
        .btn-icon {
            width: 34px; height: 34px; border-radius: 8px; padding: 0;
            font-size: 15px; border: 1.5px solid;
        }
        .btn-edit   { border-color: #cbd5e1; color: #475569; background: #f8fafc; }
        .btn-edit:hover { background: #f1f5f9; }
        .btn-delete { border-color: #fecaca; color: #dc2626; background: #fff5f5; }
        .btn-delete:hover { background: #fee2e2; }

        /* Card header row */
        .card-header-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 20px; gap: 12px;
        }

        /* ── Search bar ── */
        .search-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .search-bar select,
        .search-bar input {
            padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 13px; color: #1e293b; outline: none;
        }
        .search-bar select:focus,
        .search-bar input:focus { border-color: #2563eb; }
        .search-bar input { flex: 1; min-width: 160px; }
        .btn-search {
            padding: 8px 16px; border-radius: 8px; background: #eff6ff;
            border: 1.5px solid #bfdbfe; color: #1d4ed8; font-weight: 600;
            font-size: 13px; cursor: pointer; white-space: nowrap;
        }
        .btn-search:hover { background: #dbeafe; }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #f1f5f9; }
        th {
            text-align: left; padding: 10px 14px;
            font-size: 12px; font-weight: 700; color: #64748b;
            letter-spacing: .04em; text-transform: uppercase;
        }
        td { padding: 11px 14px; font-size: 13.5px; border-bottom: 1px solid #f1f5f9; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }
        .actions-cell { text-align: right; display: flex; gap: 6px; justify-content: flex-end; }
        .empty-row td { text-align: center; color: #94a3b8; padding: 32px; font-size: 13px; }

        /* ── Loading spinner ── */
        .spinner {
            display: inline-block; width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            vertical-align: middle; margin-right: 6px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Row highlight on add/update ── */
        @keyframes rowHighlight {
            0%   { background: #dbeafe; }
            100% { background: transparent; }
        }
        .row-highlight { animation: rowHighlight 1.8s ease forwards; }

        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .sidebar { display: none; } .main { padding: 16px; } }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h4>DASHBOARD</h4>
        <small>Account management</small>
    </div>
    <nav>
        <a href="index1.php">Home</a>
        <a href="accounts.php" class="active">Accounts</a>
        <a href="#">Reports</a>
        <a href="#">Settings</a>
        <a href="../logout.php" class="danger">Logout</a>
    </nav>
</div>

<!-- Main Content -->
<div class="main">

    <!-- Header -->
    <div class="app-header">
        <div>
            <h1>Accounts</h1>
            <p>Add, edit, delete and search users by ID or name.</p>
        </div>
        <div class="user-badge">
            <span>Logged in as</span>
            <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
        </div>
    </div>

    <!-- Alert container (populated by JS) -->
    <div id="alert-container"></div>

    <!-- Grid -->
    <div class="grid">

        <!-- Add / Edit Form -->
        <div class="card">
            <div class="card-header-row">
                <div>
                    <div class="card-title" id="form-title">Add User</div>
                    <div class="card-sub">Fill the form and submit.</div>
                </div>
                <button id="btn-cancel" class="btn btn-outline" style="display:none;">Cancel</button>
            </div>

            <form id="user-form" autocomplete="off">
                <input type="hidden" id="form-action" name="action" value="add">
                <input type="hidden" id="form-user-id" name="user_id" value="0">
                <div class="form-group">
                    <label>Name</label>
                    <input id="field-name" name="name" type="text" required placeholder="Full name">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input id="field-email" name="email" type="email" required placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input id="field-password" name="password" type="password" placeholder="Enter password">
                </div>
                <button type="submit" id="btn-submit" class="btn btn-primary">Add User</button>
            </form>
        </div>

        <!-- User List -->
        <div class="card">
            <div class="card-header-row" style="flex-wrap:wrap;">
                <div>
                    <div class="card-title">User List</div>
                    <div class="card-sub">Search by ID or name and manage account records.</div>
                </div>
                <div class="search-bar">
                    <select id="search-field">
                        <option value="name">Name / Email</option>
                        <option value="id">ID</option>
                    </select>
                    <input type="search" id="search-query" placeholder="Search users...">
                    <button type="button" id="btn-search" class="btn-search">Search</button>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-tbody">
                        <tr class="empty-row"><td colspan="4">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /grid -->
</div><!-- /main -->

<script>
$(function () {

    /* ─── Helpers ──────────────────────────────────────────────── */

    function showAlert(message, type = 'success') {
        const $a = $(`
            <div class="alert alert-${type}">
                ${escHtml(message)}
                <button class="alert-close">×</button>
            </div>
        `);
        $a.find('.alert-close').on('click', () => $a.fadeOut(200, () => $a.remove()));
        $('#alert-container').empty().append($a);
        // Auto-dismiss after 4 s
        setTimeout(() => $a.fadeOut(400, () => $a.remove()), 4000);
    }

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

    function setLoading(on) {
        const $btn = $('#btn-submit');
        if (on) {
            $btn.prop('disabled', true)
                .html('<span class="spinner"></span>Saving…');
        } else {
            $btn.prop('disabled', false)
                .text($('#form-action').val() === 'edit' ? 'Update User' : 'Add User');
        }
    }

    /* ─── Build a table row ────────────────────────────────────── */

    function buildRow(user) {
        return `
            <tr data-id="${user.ID}">
                <td>${escHtml(String(user.ID))}</td>
                <td>${escHtml(user.name)}</td>
                <td>${escHtml(user.email)}</td>
                <td>
                    <div class="actions-cell">
                        <button class="btn btn-icon btn-edit js-edit"   data-id="${user.ID}" title="Edit">✎</button>
                        <button class="btn btn-icon btn-delete js-delete" data-id="${user.ID}" title="Delete">🗑</button>
                    </div>
                </td>
            </tr>`;
    }

    /* ─── Load / Search users ──────────────────────────────────── */

    function loadUsers() {
        const q     = $('#search-query').val().trim();
        const field = $('#search-field').val();

        $.get('accounts_ajax.php', { action: 'list', search_query: q, search_field: field })
            .done(function (res) {
                const $tbody = $('#user-tbody').empty();
                if (res.success && res.users.length > 0) {
                    res.users.forEach(u => $tbody.append(buildRow(u)));
                } else {
                    $tbody.append('<tr class="empty-row"><td colspan="4">No users found.</td></tr>');
                }
            })
            .fail(function () {
                showAlert('Could not load users. Please refresh.', 'warning');
            });
    }

    // Initial load
    loadUsers();

    // Search button & Enter key
    $('#btn-search').on('click', loadUsers);
    $('#search-query').on('keydown', e => { if (e.key === 'Enter') loadUsers(); });

    /* ─── Reset form to "Add" mode ─────────────────────────────── */

    function resetForm() {
        $('#user-form')[0].reset();
        $('#form-action').val('add');
        $('#form-user-id').val('0');
        $('#form-title').text('Add User');
        $('#btn-submit').text('Add User');
        $('#field-password').attr('placeholder', 'Enter password').prop('required', true);
        $('#btn-cancel').hide();
    }

    /* ─── Cancel button ────────────────────────────────────────── */

    $('#btn-cancel').on('click', resetForm);

    /* ─── Edit button (delegated) ──────────────────────────────── */

    $('#user-tbody').on('click', '.js-edit', function () {
        const id = $(this).data('id');

        $.get('accounts_ajax.php', { action: 'get', id })
            .done(function (res) {
                if (!res.success) { showAlert(res.message, 'warning'); return; }
                const u = res.user;
                $('#form-action').val('edit');
                $('#form-user-id').val(u.ID);
                $('#field-name').val(u.name);
                $('#field-email').val(u.email);
                $('#field-password').val('').attr('placeholder', 'Leave blank to keep current').prop('required', false);
                $('#form-title').text('Edit User');
                $('#btn-submit').text('Update User');
                $('#btn-cancel').show();
                // Scroll to form on small screens
                $('html, body').animate({ scrollTop: $('#user-form').offset().top - 20 }, 300);
            })
            .fail(function () { showAlert('Could not fetch user details.', 'warning'); });
    });

    /* ─── Delete button (delegated) ────────────────────────────── */

    $('#user-tbody').on('click', '.js-delete', function () {
        if (!confirm('Delete this user permanently?')) return;
        const id  = $(this).data('id');
        const $tr = $(this).closest('tr');

        $.post('accounts_ajax.php', { action: 'delete', id })
            .done(function (res) {
                if (res.success) {
                    $tr.fadeOut(300, function () {
                        $(this).remove();
                        if ($('#user-tbody tr').length === 0) {
                            $('#user-tbody').append('<tr class="empty-row"><td colspan="4">No users found.</td></tr>');
                        }
                    });
                    showAlert(res.message);
                    // If we were editing this user, reset form
                    if (parseInt($('#form-user-id').val()) === id) resetForm();
                } else {
                    showAlert(res.message, 'warning');
                }
            })
            .fail(function () { showAlert('Delete failed. Please try again.', 'warning'); });
    });

    /* ─── Form submit (Add / Edit) ─────────────────────────────── */

    $('#user-form').on('submit', function (e) {
        e.preventDefault();
        setLoading(true);

        const formData = $(this).serialize(); // action, user_id, name, email, password

        $.post('accounts_ajax.php', formData)
            .done(function (res) {
                setLoading(false);
                if (!res.success) { showAlert(res.message, 'warning'); return; }

                showAlert(res.message);

                const u      = res.user;
                const action = $('#form-action').val();

                if (action === 'add') {
                    // Prepend new row
                    const $newRow = $(buildRow(u)).hide();
                    $('#user-tbody').find('.empty-row').remove();
                    $('#user-tbody').prepend($newRow);
                    $newRow.fadeIn(250, function () {
                        $(this).addClass('row-highlight');
                    });
                    resetForm();
                } else {
                    // Update existing row in place
                    const $row = $(`#user-tbody tr[data-id="${u.ID}"]`);
                    $row.find('td:nth-child(2)').text(u.name);
                    $row.find('td:nth-child(3)').text(u.email);
                    $row.addClass('row-highlight');
                    setTimeout(() => $row.removeClass('row-highlight'), 1800);
                    resetForm();
                }
            })
            .fail(function () {
                setLoading(false);
                showAlert('Request failed. Please try again.', 'warning');
            });
    });

});
</script>
</body>
</html>
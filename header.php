<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Bangjo' : 'Bangjo Sistem Peminjaman Alat'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #0f172a;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #dc2626;
            --light: #f9fafb;
            --light-2: #f3f4f6;
            --gray: #6b7280;
            --border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #111827;
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: var(--primary-dark);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Container */
        .container-main {
            display: flex;
            flex: 1;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 18px;
            color: var(--secondary);
        }

        .sidebar-header i {
            font-size: 24px;
            color: var(--primary);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-menu-item:hover {
            background-color: var(--light-2);
            color: var(--primary);
        }

        .sidebar-menu-item.active {
            background-color: #eff6ff;
            color: var(--primary);
            border-right: 4px solid var(--primary);
        }

        .sidebar-menu-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar-menu-item span {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu-item.logout {
            color: var(--danger);
            margin-top: 20px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }

        .sidebar-menu-item.logout:hover {
            background-color: #fee2e2;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--secondary);
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: var(--light-2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 15px;
            width: 280px;
        }

        .search-box input {
            border: none;
            background: transparent;
            width: 100%;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
        }

        .search-box i {
            color: var(--gray);
            margin-right: 8px;
        }

        .navbar-icons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--light-2);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--gray);
            transition: all 0.3s ease;
            position: relative;
        }

        .icon-btn:hover {
            background: var(--border);
            color: var(--primary);
        }

        .badge-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            background: var(--light-2);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: var(--border);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            font-size: 12px;
        }

        .user-name {
            font-weight: 600;
            color: var(--secondary);
            font-size: 13px;
        }

        .user-role {
            color: var(--gray);
            font-size: 11px;
            text-transform: capitalize;
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Breadcrumb */
        .breadcrumb-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            align-items: center;
        }

        .breadcrumb-nav a {
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        .breadcrumb-nav span {
            color: var(--gray);
        }

        .breadcrumb-nav i {
            font-size: 11px;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .page-header p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary);
        }

        .card-body {
            padding: 0;
        }

        /* Stats Card */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card-icon.primary {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .stat-card-icon.success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .stat-card-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-card-icon.danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
        }

        .stat-card-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .stat-card-label {
            color: var(--gray);
            font-size: 13px;
            font-weight: 500;
        }

        .stat-card-desc {
            color: var(--gray);
            font-size: 12px;
            margin-top: 10px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #15803d;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-2);
            color: var(--gray);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-block {
            width: 100%;
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--light-2);
        }

        thead th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--secondary);
            border-bottom: 1px solid var(--border);
        }

        tbody td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: #333;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: var(--light);
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-primary {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .badge-success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: var(--secondary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary);
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--secondary);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
            }

            .search-box {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .navbar {
                padding: 0 20px;
            }

            .search-box {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .sidebar-menu-item span {
                display: none;
            }

            .sidebar-header {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

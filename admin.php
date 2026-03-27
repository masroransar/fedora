<?php
// admin.php - لوحة تحكم الادمين المتقدمة
session_start();
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$alert_type = 'danger';

function generateSlug($text) {
    $slug = mb_strtolower(trim($text));
    $slug = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'item';
}

if (isset($conn)) {
    $colResult = $conn->query("SHOW COLUMNS FROM categories LIKE 'slug'");
    if ($colResult && $colResult->num_rows === 0) {
        $conn->query("ALTER TABLE categories ADD slug VARCHAR(255) UNIQUE AFTER name");
    }
}

// معالجة إضافة سلايدر
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_slider') {
    $slider_title = trim($_POST['slider_title'] ?? '');
    $slider_image_url = trim($_POST['slider_image_url'] ?? '');
    $slider_description = trim($_POST['slider_description'] ?? '');

    if (empty($slider_title) || empty($slider_image_url)) {
        $message = t('field_required');
    } else {
        $stmt = $conn->prepare("INSERT INTO slider_images (title, image_url, description, is_active) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $slider_title, $slider_image_url, $slider_description);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        } else {
            $message = 'خطأ: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// معالجة حذف سلايدر
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_slider') {
    $slider_id = intval($_POST['slider_id']);
    $stmt = $conn->prepare("DELETE FROM slider_images WHERE id = ?");
    $stmt->bind_param("i", $slider_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة قناة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_channel') {
    $channel_name = trim($_POST['channel_name'] ?? '');
    $channel_url = trim($_POST['channel_url'] ?? '');

    if (empty($channel_name) || empty($channel_url)) {
        $message = t('field_required');
    } else {
        $stmt = $conn->prepare("INSERT INTO channels (name, url) VALUES (?, ?)");
        $stmt->bind_param("ss", $channel_name, $channel_url);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة حذف قناة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_channel') {
    $channel_id = intval($_POST['channel_id']);
    $stmt = $conn->prepare("DELETE FROM channels WHERE id = ?");
    $stmt->bind_param("i", $channel_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة فيلم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_movie') {
    $movie_title = trim($_POST['movie_title'] ?? '');
    $movie_video_url = trim($_POST['movie_video_url'] ?? '');
    $movie_thumbnail = trim($_POST['movie_thumbnail'] ?? '');
    $movie_description = trim($_POST['movie_description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 1);

    if (empty($movie_title) || empty($movie_video_url)) {
        $message = t('field_required');
    } else {
        $stmt = $conn->prepare("INSERT INTO movies (title, description, video_url, thumbnail_url, category_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssi", $movie_title, $movie_description, $movie_video_url, $movie_thumbnail, $category_id);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة حذف فيلم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_movie') {
    $movie_id = intval($_POST['movie_id']);
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة مسلسل
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_series') {
    $series_title = trim($_POST['series_title'] ?? '');
    $series_thumbnail = trim($_POST['series_thumbnail'] ?? '');
    $series_description = trim($_POST['series_description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 1);

    if (empty($series_title) || empty($series_thumbnail)) {
        $message = t('field_required');
    } else {
        $stmt = $conn->prepare("INSERT INTO series (title, description, thumbnail_url, category_id, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("sssi", $series_title, $series_description, $series_thumbnail, $category_id);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة حذف مسلسل
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_series') {
    $series_id = intval($_POST['series_id']);
    $stmt = $conn->prepare("DELETE FROM series WHERE id = ?");
    $stmt->bind_param("i", $series_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة قسم (Category)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_category') {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_slug = trim($_POST['category_slug'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');

    if (empty($category_name)) {
        $message = t('field_required');
    } else {
        if (empty($category_slug)) {
            $category_slug = generateSlug($category_name);
        }

        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $category_name, $category_slug, $category_description);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        } else {
            $message = 'خطأ: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// معالجة حذف قسم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_category') {
    $category_id = intval($_POST['category_id']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة سوشيال ميديا
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_social') {
    $social_name = trim($_POST['social_name'] ?? '');
    $social_icon = trim($_POST['social_icon'] ?? '');
    $social_url = trim($_POST['social_url'] ?? '');

    if (empty($social_name) || empty($social_url)) {
        $message = t('field_required');
    } else {
        $stmt = $conn->prepare("INSERT INTO social_links (platform, icon_class, url, is_active) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $social_name, $social_icon, $social_url);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة حذف سوشيال ميديا
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_social') {
    $social_id = intval($_POST['social_id']);
    $stmt = $conn->prepare("DELETE FROM social_links WHERE id = ?");
    $stmt->bind_param("i", $social_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة القائمة الجانبية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_sidebar') {
    $sidebar_label = trim($_POST['sidebar_label'] ?? '');
    $sidebar_icon = trim($_POST['sidebar_icon'] ?? '');
    $sidebar_route = trim($_POST['sidebar_route'] ?? '');

    if (empty($sidebar_label) || empty($sidebar_route)) {
        $message = 'التسمية و المسار مطلوبة';
    } else {
        $stmt = $conn->prepare("INSERT INTO sidebar_items (title, icon_class, link, is_active) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $sidebar_label, $sidebar_icon, $sidebar_route);
        if ($stmt->execute()) {
            $message = 'تم إضافة العنصر بنجاح!';
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة حذف القائمة الجانبية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_sidebar') {
    $sidebar_id = intval($_POST['sidebar_id']);
    $stmt = $conn->prepare("DELETE FROM sidebar_items WHERE id = ?");
    $stmt->bind_param("i", $sidebar_id);
    if ($stmt->execute()) {
        $message = t('deleted');
        $alert_type = 'success';
    }
    $stmt->close();
}

// معالجة إضافة مستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_user') {
    $new_username = trim($_POST['new_username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $new_phone = trim($_POST['new_phone'] ?? '');
    $new_role = $_POST['new_role'] ?? 'user';

    if (empty($new_username) || empty($new_password) || empty($new_phone)) {
        $message = t('field_required');
    } elseif (strlen($new_password) < 6) {
        $message = t('password_min_length');
    } else {
        $hashed_password = hash_password($new_password);
        $stmt = $conn->prepare("INSERT INTO users (username, password, phone, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $new_username, $hashed_password, $new_phone, $new_role);
        if ($stmt->execute()) {
            $message = t('created');
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة حذف مستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $user_id = intval($_POST['user_id']);
    if ($user_id == $_SESSION['user_id']) {
        $message = 'لا يمكنك حذف حسابك الخاص';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = t('deleted');
            $alert_type = 'success';
        }
        $stmt->close();
    }
}

// معالجة تغيير دور المستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_user_role') {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    if ($stmt->execute()) {
        $message = 'تم تحديث الدور بنجاح!';
        $alert_type = 'success';
    }
    $stmt->close();
}

// جلب البيانات
$sliders = $conn->query("SELECT * FROM slider_images ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$channels = $conn->query("SELECT * FROM channels ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$movies = $conn->query("SELECT * FROM movies ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$series_list = $conn->query("SELECT * FROM series ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$social_links = $conn->query("SELECT * FROM social_links ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$sidebar_items = $conn->query("SELECT * FROM sidebar_items ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo get_html_lang(); ?>" dir="<?php echo get_text_direction(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('admin_panel'); ?> - FEDORA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1F3A93;
            --secondary: #2E5090;
            --accent: #F8F9FA;
            --danger: #dc3545;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--accent);
            color: #333;
        }

        /* Sidebar Navigation */
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            right: 0;
            top: 0;
            box-shadow: -2px 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 30px;
            padding: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 14px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(-5px);
        }

        .main-content {
            margin-right: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        /* Header */
        .top-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header h1 {
            color: var(--primary);
            font-weight: bold;
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
        }

        /* Alert Messages */
        .alert-custom {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Cards */
        .section-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
            border-top: 4px solid var(--primary);
        }

        .section-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header i {
            font-size: 20px;
        }

        .section-body {
            padding: 25px;
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(31, 58, 147, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #162b6f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31, 58, 147, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            border: none;
            border-radius: 8px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-info {
            background: var(--secondary);
            border: none;
            border-radius: 8px;
            color: white;
        }

        .btn-info:hover {
            background: #1f4a73;
        }

        /* Tables */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            margin: 0;
            font-size: 13px;
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .table tbody tr {
            border-color: #eee;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .table td, .table th {
            padding: 15px;
            vertical-align: middle;
        }

        /* Badges */
        .badge-custom {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-right: 0;
            }
        }

        .stats-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            border-top: 3px solid var(--primary);
        }

        .stats-box .count {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
        }

        .stats-box .label {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-film"></i> FEDORA
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#home" data-section="home">
                <i class="fas fa-home"></i> <?php echo t('dashboard'); ?>
            </a>
            <a class="nav-link" href="#sliders" data-section="sliders">
                <i class="fas fa-images"></i> <?php echo t('manage_sliders'); ?>
            </a>
            <a class="nav-link" href="#channels" data-section="channels">
                <i class="fas fa-tv"></i> <?php echo t('manage_channels'); ?>
            </a>
            <a class="nav-link" href="#movies" data-section="movies">
                <i class="fas fa-film"></i> <?php echo t('manage_movies'); ?>
            </a>
            <a class="nav-link" href="#series" data-section="series">
                <i class="fas fa-film"></i> <?php echo t('manage_series'); ?>
            </a>
            <a class="nav-link" href="#categories" data-section="categories">
                <i class="fas fa-list"></i> <?php echo t('manage_categories'); ?>
            </a>
            <a class="nav-link" href="#social" data-section="social">
                <i class="fas fa-share-alt"></i> <?php echo t('manage_social'); ?>
            </a>
            <a class="nav-link" href="#menu" data-section="menu">
                <i class="fas fa-bars"></i> <?php echo t('manage_sidebar'); ?>
            </a>
            <a class="nav-link" href="#users" data-section="users">
                <i class="fas fa-users"></i> <?php echo t('manage_users'); ?>
            </a>
            <div style="margin-top: auto; padding: 20px; border-top: 2px solid rgba(255,255,255,0.2);">
                <a href="logout.php" class="nav-link text-warning">
                    <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="top-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> <?php echo t('admin_dashboard'); ?></h1>
                <small class="text-muted"><?php echo t('welcome'); ?> <?php echo htmlspecialchars($_SESSION['username']); ?></small>
            </div>
            <div class="user-profile">
                <div>
                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong><br>
                    <small class="text-muted"><?php echo t('admin'); ?></small>
                </div>
                <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $alert_type; ?> alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo ($alert_type == 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div id="home" class="section">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-box">
                        <div class="count"><?php echo count($users); ?></div>
                        <div class="label">المستخدمين</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-box">
                        <div class="count"><?php echo count($movies); ?></div>
                        <div class="label">الأفلام</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-box">
                        <div class="count"><?php echo count($series_list); ?></div>
                        <div class="label">المسلسلات</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-box">
                        <div class="count"><?php echo count($channels); ?></div>
                        <div class="label">القنوات</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sliders Section -->
        <div id="sliders" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-images"></i> إضافة صورة متحركة جديدة
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_slider">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" class="form-control" name="slider_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط الصورة</label>
                                <input type="url" class="form-control" name="slider_image_url" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea class="form-control" name="slider_description" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة الصور المتحركة (<?php echo count($sliders); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الصورة</th>
                                    <th>العنوان</th>
                                    <th>الوصف</th>
                                    <th>التاريخ</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sliders as $slider): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($slider['image_url']); ?>" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover;"></td>
                                    <td><?php echo htmlspecialchars($slider['title']); ?></td>
                                    <td><?php echo substr($slider['description'] ?? '', 0, 50); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($slider['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_slider">
                                            <input type="hidden" name="slider_id" value="<?php echo $slider['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channels Section -->
        <div id="channels" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة قناة مباشرة جديدة
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_channel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم القناة</label>
                                <input type="text" class="form-control" name="channel_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط البث (M3U8)</label>
                                <input type="url" class="form-control" name="channel_url" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">القسم</label>
                                <select class="form-select" name="category_id">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة القناة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة القنوات (<?php echo count($channels); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>اسم القناة</th>
                                    <th>الرابط</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($channels as $channel): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($channel['name']); ?></strong></td>
                                    <td><small><?php echo substr($channel['url'], 0, 50); ?></small></td>
                                    <td><span class="badge bg-success">مفعل</span></td>
                                    <td><?php echo date('Y-m-d', strtotime($channel['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_channel">
                                            <input type="hidden" name="channel_id" value="<?php echo $channel['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movies Section -->
        <div id="movies" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة فيلم جديد
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_movie">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">عنوان الفيلم</label>
                                <input type="text" class="form-control" name="movie_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">القسم</label>
                                <select class="form-select" name="category_id">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط الفيديو (MP4)</label>
                                <input type="url" class="form-control" name="movie_video_url" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط الصورة المصغرة</label>
                                <input type="url" class="form-control" name="movie_thumbnail">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea class="form-control" name="movie_description" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة الفيلم
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة الأفلام (<?php echo count($movies); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الصورة</th>
                                    <th>العنوان</th>
                                    <th>الوصف</th>
                                    <th>التاريخ</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movies as $movie): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($movie['thumbnail_url'] ?? ''); ?>" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover; background: #ddd;"></td>
                                    <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                    <td><?php echo substr($movie['description'] ?? '', 0, 50); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($movie['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_movie">
                                            <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Series Section -->
        <div id="series" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة مسلسل جديد
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_series">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">عنوان المسلسل</label>
                                <input type="text" class="form-control" name="series_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">القسم</label>
                                <select class="form-select" name="category_id">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط الفيديو (MP4)</label>
                                <input type="url" class="form-control" name="series_video_url" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط الصورة المصغرة</label>
                                <input type="url" class="form-control" name="series_thumbnail">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea class="form-control" name="series_description" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة المسلسل
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة المسلسلات (<?php echo count($series_list); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الصورة</th>
                                    <th>العنوان</th>
                                    <th>الوصف</th>
                                    <th>التاريخ</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($series_list as $series): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($series['thumbnail_url'] ?? ''); ?>" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover; background: #ddd;"></td>
                                    <td><?php echo htmlspecialchars($series['title']); ?></td>
                                    <td><?php echo substr($series['description'] ?? '', 0, 50); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($series['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_series">
                                            <input type="hidden" name="series_id" value="<?php echo $series['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Section -->
        <div id="categories" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة قسم جديد
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_category">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم القسم</label>
                                <input type="text" class="form-control" name="category_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الاختصار (Slug)</label>
                                <input type="text" class="form-control" name="category_slug">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea class="form-control" name="category_description" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة القسم
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة الأقسام (<?php echo count($categories); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>الاختصار</th>
                                    <th>الوصف</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['name'] ?? ''); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($cat['slug'] ?? generateSlug($cat['name'] ?? '')); ?></code></td>
                                    <td><?php echo substr($cat['description'] ?? '', 0, 50); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Section -->
        <div id="social" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة رابط سوشيال ميديا جديد
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_social">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الاسم (Facebook, Twitter...)</label>
                                <input type="text" class="form-control" name="social_name" placeholder="Facebook" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الأيقونة</label>
                                <input type="text" class="form-control" name="social_icon" placeholder="facebook-f" value="facebook-f">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الرابط</label>
                                <input type="url" class="form-control" name="social_url" required>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة روابط السوشيال (<?php echo count($social_links); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>الأيقونة</th>
                                    <th>الرابط</th>
                                    <th>الحالة</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($social_links as $social): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($social['name'] ?? $social['platform'] ?? ''); ?></strong></td>
                                    <td><i class="fab fa-<?php echo htmlspecialchars($social['icon_class'] ?? $social['icon'] ?? ''); ?>" style="font-size: 18px;"></i></td>
                                    <td><small><?php echo substr($social['url'] ?? '', 0, 40); ?></small></td>
                                    <td><span class="badge bg-success">مفعل</span></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_social">
                                            <input type="hidden" name="social_id" value="<?php echo $social['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu Section -->
        <div id="menu" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة عنصر القائمة الجانبية جديد
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_sidebar">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">التسمية</label>
                                <input type="text" class="form-control" name="sidebar_label" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">الأيقونة</label>
                                <input type="text" class="form-control" name="sidebar_icon" placeholder="fas fa-movie">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">المسار (Route)</label>
                                <input type="text" class="form-control" name="sidebar_route" placeholder="movies" required>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> عناصر القائمة (<?php echo count($sidebar_items); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التسمية</th>
                                    <th>الأيقونة</th>
                                    <th>المسار</th>
                                    <th>الحالة</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sidebar_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['label']); ?></td>
                                    <td><i class="<?php echo htmlspecialchars($item['icon']); ?>"></i></td>
                                    <td><code><?php echo htmlspecialchars($item['route']); ?></code></td>
                                    <td><span class="badge bg-success">مفعل</span></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_sidebar">
                                            <input type="hidden" name="sidebar_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div id="users" class="section" style="display:none;">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-plus-circle"></i> إضافة مستخدم جديد
                </div>
                <div class="section-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_user">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم المستخدم</label>
                                <input type="text" class="form-control" name="new_username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف (07xxxxxxxx)</label>
                                <input type="tel" class="form-control" name="new_phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الدور</label>
                                <select class="form-select" name="new_role">
                                    <option value="user">مستخدم عادي</option>
                                    <option value="admin">أدمين</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> إضافة المستخدم
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-list"></i> قائمة المستخدمين (<?php echo count($users); ?>)
                </div>
                <div class="section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>اسم المستخدم</th>
                                    <th>رقم الهاتف</th>
                                    <th>الدور الحالي</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update_user_role">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="new_role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>مستخدم</option>
                                                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>أدمين</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
                
                // Add active to clicked
                this.classList.add('active');
                
                // Show section
                const section = document.getElementById(this.dataset.section);
                if (section) section.style.display = 'block';
            });
        });

        // Show home by default
        document.getElementById('home').style.display = 'block';
    </script>
</body>
</html>
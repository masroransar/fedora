<?php
// admin_content.php - Enhanced admin panel for content management
session_start();
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$alert_type = '';

// ============ SLIDER MANAGEMENT ============
if ($_POST['section'] ?? null === 'slider') {
    if ($_POST['action'] === 'add') {
        $title = trim($_POST['slider_title'] ?? '');
        $image_url = trim($_POST['slider_image_url'] ?? '');
        $link_url = trim($_POST['slider_link_url'] ?? '');
        $description = trim($_POST['slider_description'] ?? '');
        
        if (empty($title) || empty($image_url)) {
            $message = t('field_required');
            $alert_type = 'danger';
        } else {
            $stmt = $conn->prepare("INSERT INTO slider_images (title, image_url, link_url, description, is_active, sort_order) VALUES (?, ?, ?, ?, 1, 0)");
            $stmt->bind_param("ssss", $title, $image_url, $link_url, $description);
            if ($stmt->execute()) {
                $message = t('created');
                $alert_type = 'success';
            } else {
                $message = 'خطأ: ' . $stmt->error;
                $alert_type = 'danger';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['slider_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM slider_images WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = t('deleted');
            $alert_type = 'success';
        } else {
            $message = t('error');
            $alert_type = 'danger';
        }
        $stmt->close();
    }
}

// ============ CHANNELS MANAGEMENT ============
if ($_POST['section'] ?? null === 'channels') {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['channel_name'] ?? '');
        $url = trim($_POST['channel_url'] ?? '');
        $image_url = trim($_POST['channel_image_url'] ?? '');
        
        if (empty($name) || empty($url)) {
            $message = 'اسم القناة و رابط البث المباشر مطلوبة';
            $alert_type = 'danger';
        } else {
            $stmt = $conn->prepare("INSERT INTO channels (name, url, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $url, $image_url);
            if ($stmt->execute()) {
                $message = '✓ تم إضافة القناة بنجاح';
                $alert_type = 'success';
            } else {
                $message = 'خطأ: ' . $stmt->error;
                $alert_type = 'danger';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['channel_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM channels WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = '✓ تم حذف القناة بنجاح';
            $alert_type = 'success';
        } else {
            $message = 'خطأ في الحذف';
            $alert_type = 'danger';
        }
        $stmt->close();
    }
}

// ============ CATEGORIES MANAGEMENT ============
if ($_POST['section'] ?? null === 'categories') {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['category_description'] ?? '');
        $icon = trim($_POST['category_icon'] ?? '');
        
        if (empty($name)) {
            $message = 'اسم القسم مطلوب';
            $alert_type = 'danger';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, icon_class, is_active, sort_order) VALUES (?, ?, ?, 1, 0)");
            $stmt->bind_param("sss", $name, $description, $icon);
            if ($stmt->execute()) {
                $message = '✓ تم إضافة القسم بنجاح';
                $alert_type = 'success';
            } else {
                $message = 'خطأ: ' . $stmt->error;
                $alert_type = 'danger';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['category_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = '✓ تم حذف القسم بنجاح';
            $alert_type = 'success';
        } else {
            $message = 'خطأ في الحذف';
            $alert_type = 'danger';
        }
        $stmt->close();
    }
}

// Fetch data
$sliders = $conn->query("SELECT * FROM slider_images ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$channels = $conn->query("SELECT * FROM channels ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$current_tab = $_GET['tab'] ?? 'slider';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المحتوى - FEDORA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container-main {
            max-width: 1200px;
            margin-top: 30px;
        }
        
        .card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .alert {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .table {
            background: white;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: none;
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }
        
        .btn-sm {
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3><i class="fas fa-cogs"></i> إدارة محتوى FEDORA</h3>
                            </div>
                            <div class="col text-end">
                                <a href="logout.php" class="btn btn-light btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="card mb-4">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= $current_tab === 'slider' ? 'active' : '' ?>" href="?tab=slider">
                        <i class="fas fa-images"></i> صور الشريط
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_tab === 'channels' ? 'active' : '' ?>" href="?tab=channels">
                        <i class="fas fa-tv"></i> القنوات
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_tab === 'categories' ? 'active' : '' ?>" href="?tab=categories">
                        <i class="fas fa-folder"></i> الأقسام
                    </a>
                </li>
            </ul>
        </div>

        <!-- TAB: SLIDERS -->
        <?php if ($current_tab === 'slider'): ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> إضافة صورة شريط جديدة</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="section" value="slider">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" class="form-control" name="slider_title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">رابط الصورة</label>
                                <input type="url" class="form-control" name="slider_image_url" required placeholder="https://...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">رابط الربط (اختياري)</label>
                                <input type="text" class="form-control" name="slider_link_url" placeholder="/movies">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea class="form-control" name="slider_description" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> صور الشريط الموجودة (<?= count($sliders) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sliders)): ?>
                        <p class="text-muted text-center">لا توجد صور</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الصورة</th>
                                        <th>العنوان</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sliders as $slider): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= htmlspecialchars($slider['image_url']) ?>" 
                                                 style="max-width: 60px; max-height: 40px; border-radius: 4px;" 
                                                 alt="<?= htmlspecialchars($slider['title']) ?>">
                                        </td>
                                        <td><?= htmlspecialchars($slider['title']) ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?= $slider['is_active'] ? 'مفعل' : 'معطل' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="section" value="slider">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="slider_id" value="<?= $slider['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('هل متأكد من الحذف؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: CHANNELS -->
        <?php if ($current_tab === 'channels'): ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> إضافة قناة جديدة</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="section" value="channels">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label class="form-label">اسم القناة</label>
                                <input type="text" class="form-control" name="channel_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">رابط البث المباشر (M3U8)</label>
                                <input type="url" class="form-control" name="channel_url" required placeholder="https://...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">رابط الصورة (شعار)</label>
                                <input type="url" class="form-control" name="channel_image_url" placeholder="https://...">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> القنوات الموجودة (<?= count($channels) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($channels)): ?>
                        <p class="text-muted text-center">لا توجد قنوات</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الصورة</th>
                                        <th>الاسم</th>
                                        <th>الرابط</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($channels as $channel): ?>
                                    <tr>
                                        <td>
                                            <?php if ($channel['image_url']): ?>
                                            <img src="<?= htmlspecialchars($channel['image_url']) ?>" 
                                                 style="max-width: 60px; max-height: 40px; border-radius: 4px;" 
                                                 alt="<?= htmlspecialchars($channel['name']) ?>">
                                            <?php else: ?>
                                            <i class="fas fa-image text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($channel['name']) ?></td>
                                        <td>
                                            <small><code><?= substr($channel['url'], 0, 40) ?>...</code></small>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="section" value="channels">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="channel_id" value="<?= $channel['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('هل متأكد من الحذف؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: CATEGORIES -->
        <?php if ($current_tab === 'categories'): ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> إضافة قسم جديد</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="section" value="categories">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label class="form-label">اسم القسم</label>
                                <input type="text" class="form-control" name="category_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea class="form-control" name="category_description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">أيقونة CSS</label>
                                <input type="text" class="form-control" name="category_icon" placeholder="fas fa-film">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> الأقسام الموجودة (<?= count($categories) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                        <p class="text-muted text-center">لا توجد أقسام</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الأيقونة</th>
                                        <th>الاسم</th>
                                        <th>الوصف</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td>
                                            <?php if ($cat['icon_class']): ?>
                                            <i class="<?= htmlspecialchars($cat['icon_class']) ?>" style="font-size: 18px;"></i>
                                            <?php else: ?>
                                            <i class="fas fa-folder" style="font-size: 18px;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($cat['name']) ?></td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 40)) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?= $cat['is_active'] ? 'مفعل' : 'معطل' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="section" value="categories">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('هل متأكد من الحذف؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="row mt-5 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted">
                        <small>FEDORA Content Management System © 2026 — جميع الحقوق محفوظة</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>

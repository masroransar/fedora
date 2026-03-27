<?php
require 'config.php';

echo "<h2>إضافة بيانات اختبارية</h2>";

// إضافة قنوات
$channels = [
    ['MBC', 'https://example.com/mbc.m3u8'],
    ['Al Jazeera', 'https://example.com/aljazeera.m3u8'],
    ['BBC', 'https://example.com/bbc.m3u8'],
];

echo "<h3>إضافة القنوات:</h3>";
foreach ($channels as $ch) {
    $stmt = $conn->prepare("INSERT IGNORE INTO channels (name, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $ch[0], $ch[1]);
    if ($stmt->execute()) {
        echo "<p>✓ تمت إضافة قناة: {$ch[0]}</p>";
    }
    $stmt->close();
}

// إضافة أفلام
$movies = [
    ['فيلم اختبار 1', 'فيلم اختبار رائع', 'https://example.com/movie1.mp4', 'https://example.com/thumb1.jpg', 1],
    ['فيلم اختبار 2', 'فيلم اختبار آخر', 'https://example.com/movie2.mp4', 'https://example.com/thumb2.jpg', 1],
];

echo "<h3>إضافة الأفلام:</h3>";
foreach ($movies as $movie) {
    $stmt = $conn->prepare("INSERT INTO movies (title, description, video_url, thumbnail_url, category_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssi", $movie[0], $movie[1], $movie[2], $movie[3], $movie[4]);
    if ($stmt->execute()) {
        echo "<p>✓ تمت إضافة فيلم: {$movie[0]}</p>";
    } else {
        echo "<p>✗ خطأ: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// إضافة مسلسلات
$series = [
    ['مسلسل اختبار 1', 'مسلسل اختبار رائع', 'https://example.com/series1_thumb.jpg', 1, 10],
    ['مسلسل اختبار 2', 'مسلسل اختبار آخر', 'https://example.com/series2_thumb.jpg', 1, 20],
];

echo "<h3>إضافة المسلسلات:</h3>";
foreach ($series as $ser) {
    $stmt = $conn->prepare("INSERT INTO series (title, description, thumbnail_url, category_id, total_episodes, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssii", $ser[0], $ser[1], $ser[2], $ser[3], $ser[4]);
    if ($stmt->execute()) {
        echo "<p>✓ تمت إضافة مسلسل: {$ser[0]}</p>";
    } else {
        echo "<p>✗ خطأ: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// إضافة صور متحركة
$sliders = [
    ['فيلم جديد', 'تابع أحدث الأفلام', 'https://example.com/slider1.jpg'],
    ['مسلسل حصري', 'اكتشف مسلسلاتنا الحصرية', 'https://example.com/slider2.jpg'],
];

echo "<h3>إضافة الصور المتحركة:</h3>";
foreach ($sliders as $slider) {
    $stmt = $conn->prepare("INSERT INTO slider_images (title, description, image_url, is_active) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $slider[0], $slider[1], $slider[2]);
    if ($stmt->execute()) {
        echo "<p>✓ تمت إضافة صورة: {$slider[0]}</p>";
    }
    $stmt->close();
}

echo "<hr><h3>✓ تمت إضافة جميع البيانات بنجاح!</h3>";
echo "<p><a href='test_endpoints.html'>اختبر API</a> | <a href='admin.php'>لوحة التحكم</a></p>";

$conn->close();
?>

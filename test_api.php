<?php
echo "✓ API اختبار سريع\n\n";

// اختبار الاتصال بقاعدة البيانات
require 'config.php';

echo "✓ تم الاتصال بقاعدة البيانات\n";

// اختبار جدول القنوات
$result = $conn->query("SELECT COUNT(*) as count FROM channels");
$row = $result->fetch_assoc();
echo "✓ عدد القنوات: " . $row['count'] . "\n";

// اختبار جدول الفئات
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$row = $result->fetch_assoc();
echo "✓ عدد الفئات: " . $row['count'] . "\n";

// اختبار جدول الأفلام
$result = $conn->query("SELECT COUNT(*) as count FROM movies");
$row = $result->fetch_assoc();
echo "✓ عدد الأفلام: " . $row['count'] . "\n";

// اختبار جدول المستخدمين
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "✓ عدد المستخدمين: " . $row['count'] . "\n";

echo "\n✓ جميع الجداول تعمل بنجاح!\n";

$conn->close();
?>

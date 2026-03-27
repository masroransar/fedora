<?php
$languages = [
    'en' => [
        'app_name' => 'FEDORA',
        'admin_panel' => 'Admin Panel',
        'admin_dashboard' => 'Admin Dashboard',
        'logout' => 'Logout',
        'login' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'email' => 'Email',
        'phone' => 'Phone',
        'profile' => 'Profile',
        'settings' => 'Settings',
        'manage_users' => 'Manage Users',
        'manage_sliders' => 'Manage Sliders',
        'manage_channels' => 'Manage Channels',
        'manage_movies' => 'Manage Movies',
        'manage_series' => 'Manage Series',
        'manage_categories' => 'Manage Categories',
        'manage_social' => 'Manage Social Links',
        'manage_sidebar' => 'Manage Sidebar',
        'add_slider' => 'Add Slider',
        'add_channel' => 'Add Channel',
        'add_movie' => 'Add Movie',
        'add_series' => 'Add Series',
        'add_category' => 'Add Category',
        'add_social' => 'Add Social Link',
        'add_user' => 'Add User',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'title' => 'Title',
        'name' => 'Name',
        'url' => 'URL',
        'description' => 'Description',
        'image_url' => 'Image URL',
        'video_url' => 'Video URL',
        'm3u8_url' => 'M3U8 URL',
        'link_url' => 'Link URL',
        'category' => 'Category',
        'sort_order' => 'Sort Order',
        'status' => 'Status',
        'actions' => 'Actions',
        'created_at' => 'Created At',
        'updated_at' => 'Updated',
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'confirm_delete' => 'Are you sure you want to delete this?',
        'deleted' => 'Deleted successfully',
        'updated' => 'Updated successfully',
        'created' => 'Created successfully',
        'select_language' => 'Select Language',
        'english' => 'English',
        'arabic' => 'العربية',
        'kurdish' => 'کوردی',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'profile_picture' => 'Profile Picture',
        'upload' => 'Upload',
        'users' => 'Users',
        'sliders' => 'Sliders',
        'channels' => 'Channels',
        'movies' => 'Movies',
        'series' => 'Series',
        'categories' => 'Categories',
        'social_links' => 'Social Links',
        'sidebar_items' => 'Sidebar Items',
        'role' => 'Role',
        'admin' => 'Admin',
        'user' => 'User',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'all_sliders' => 'All Sliders',
        'all_channels' => 'All Channels',
        'all_movies' => 'All Movies',
        'all_series' => 'All Series',
        'all_categories' => 'All Categories',
        'total_users' => 'Total Users',
        'total_content' => 'Total Content',
        'recent_activities' => 'Recent Activities',
        'field_required' => 'This field is required',
        'invalid_email' => 'Invalid email',
        'password_mismatch' => 'Passwords do not match',
        'already_exists' => 'Already exists',
        'not_found' => 'Not found',
        'access_denied' => 'Access denied',
        'network_error' => 'Network error',
        'server_error' => 'Server error',
    ]
];

// Get language from URL parameter, cookie, or session
$lang = $_GET['lang'] ?? $_COOKIE['app_language'] ?? $_SESSION['app_language'] ?? 'en';
$lang = in_array($lang, ['en', 'ar', 'ku']) ? $lang : 'en';

// Set cookie
setcookie('app_language', $lang, time() + (86400 * 30), '/');

// Helper function to translate
function t($key, $replacements = []) {
    global $lang, $languages;
    
    $text = $languages[$lang][$key] ?? $languages['en'][$key] ?? $key;
    
    foreach ($replacements as $search => $replace) {
        $text = str_replace(':' . $search, $replace, $text);
    }
    
    return $text;
}

function is_rtl_language($language = null) {
    global $lang;
    $language = $language ?? $lang;
    return in_array($language, ['ar', 'ku']);
}

function get_text_direction($language = null) {
    return is_rtl_language($language) ? 'rtl' : 'ltr';
}
?>

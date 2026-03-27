-- Create slider_images table
CREATE TABLE IF NOT EXISTS slider_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert 4 default slider images
INSERT INTO slider_images (title, image_url, description, sort_order, is_active) VALUES
('Welcome to Fedora Platform', 'https://via.placeholder.com/600x300?text=Welcome+To+Fedora', 'Your trusted platform for social connection', 1, 1),
('Amazing Features Waiting', 'https://via.placeholder.com/600x300?text=Amazing+Features', 'Discover powerful features designed for you', 2, 1),
('Easy & Intuitive Interface', 'https://via.placeholder.com/600x300?text=Easy+To+Use', 'Simple design for everyone to enjoy', 3, 1),
('Secure & Fast Service', 'https://via.placeholder.com/600x300?text=Secure+And+Fast', 'Your data is protected with latest security', 4, 1);

-- Check if data inserted successfully
SELECT * FROM slider_images;

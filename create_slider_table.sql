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

-- Insert default slider images
INSERT INTO slider_images (title, image_url, description, sort_order) VALUES
('Welcome to Fedora', 'https://via.placeholder.com/600x300?text=Welcome+to+Fedora', 'Best platform for you', 1),
('Amazing Features', 'https://via.placeholder.com/600x300?text=Amazing+Features', 'Discover our features', 2),
('Easy to Use', 'https://via.placeholder.com/600x300?text=Easy+to+Use', 'Simple and intuitive', 3),
('Secure & Fast', 'https://via.placeholder.com/600x300?text=Secure+And+Fast', 'Secure and fast service', 4);

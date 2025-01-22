## SQL for the Database ##

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Hotels table
CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Traffic data table
CREATE TABLE traffic_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT,
    date DATE NOT NULL,
    expected_traffic INT DEFAULT 0,
    new_users INT DEFAULT 0,
    number_of_bookings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    UNIQUE KEY unique_hotel_date (hotel_id, date)
);

-- Booking data table
CREATE TABLE booking_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT,
    date DATE NOT NULL,
    number_of_rooms INT DEFAULT 0,
    booking_target INT DEFAULT 0,
    actual_bookings INT DEFAULT 0,
    booked_nights INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    UNIQUE KEY unique_hotel_date (hotel_id, date)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

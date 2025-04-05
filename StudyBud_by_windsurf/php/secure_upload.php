<?php
/**
 * Secure File Upload Handler
 * 
 * This file provides functions for secure file uploads, including:
 * - File type validation
 * - File size validation
 * - Image dimension validation
 * - Secure filename generation
 * - MIME type verification
 */

/**
 * Validates and processes a file upload securely
 * 
 * @param array $file The $_FILES array element
 * @param array $allowed_types Array of allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @param string $upload_dir Directory to upload to (must end with trailing slash)
 * @param array $options Additional options (min_width, min_height, max_width, max_height)
 * @return array Result with status, message, and filename if successful
 */
function secure_upload_file($file, $allowed_types, $max_size, $upload_dir, $options = []) {
    // Initialize result array
    $result = [
        'status' => false,
        'message' => '',
        'filename' => ''
    ];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload failed with error code: ' . $file['error'];
        return $result;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $result['message'] = 'File size exceeds the maximum allowed size of ' . ($max_size / 1024 / 1024) . 'MB';
        return $result;
    }
    
    // Verify MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_mime = $finfo->file($file['tmp_name']);
    
    if (!in_array($file_mime, $allowed_types)) {
        $result['message'] = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        return $result;
    }
    
    // For images, verify dimensions if options provided
    if (strpos($file_mime, 'image/') === 0) {
        $image_info = getimagesize($file['tmp_name']);
        
        if ($image_info === false) {
            $result['message'] = 'Invalid image file';
            return $result;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        
        // Check minimum dimensions
        if (isset($options['min_width']) && $width < $options['min_width']) {
            $result['message'] = 'Image width is too small. Minimum width: ' . $options['min_width'] . 'px';
            return $result;
        }
        
        if (isset($options['min_height']) && $height < $options['min_height']) {
            $result['message'] = 'Image height is too small. Minimum height: ' . $options['min_height'] . 'px';
            return $result;
        }
        
        // Check maximum dimensions
        if (isset($options['max_width']) && $width > $options['max_width']) {
            $result['message'] = 'Image width is too large. Maximum width: ' . $options['max_width'] . 'px';
            return $result;
        }
        
        if (isset($options['max_height']) && $height > $options['max_height']) {
            $result['message'] = 'Image height is too large. Maximum height: ' . $options['max_height'] . 'px';
            return $result;
        }
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $result['message'] = 'Failed to create upload directory';
            return $result;
        }
    }
    
    // Generate a secure filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = bin2hex(random_bytes(16)) . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move the file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $result['message'] = 'Failed to move uploaded file';
        return $result;
    }
    
    // Set file permissions
    chmod($upload_path, 0644);
    
    // Return success
    $result['status'] = true;
    $result['message'] = 'File uploaded successfully';
    $result['filename'] = $new_filename;
    
    return $result;
}

/**
 * Validates and processes an image upload securely
 * 
 * @param array $file The $_FILES array element
 * @param int $max_size Maximum file size in bytes (default 2MB)
 * @param string $upload_dir Directory to upload to (must end with trailing slash)
 * @param array $options Additional options (min_width, min_height, max_width, max_height)
 * @return array Result with status, message, and filename if successful
 */
function secure_upload_image($file, $max_size = 2097152, $upload_dir = 'uploads/images/', $options = []) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    return secure_upload_file($file, $allowed_types, $max_size, $upload_dir, $options);
}
?>

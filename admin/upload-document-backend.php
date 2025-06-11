<?php
session_start(); // Start the session

require_once '../config/db-config.php';

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to handle file uploads with validation
function uploadFile($file, $uploadDir, $allowedMimeTypes, $maxSize = 5242880) {
    if (!empty($file['name'])) {
        if ($file['size'] > $maxSize) {
            return ["status" => "error", "message" => "File size exceeds 5MB: " . $file['name']];
        }

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedMimeTypes)) {
            return ["status" => "error", "message" => "Invalid file type for: " . $file['name']];
        }

        // Generate a unique file name without the path
        $fileName = uniqid() . "-" . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return ["status" => "error", "message" => "Failed to create upload directory."];
            }
        }

        // Move the file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ["status" => "success", "file_name" => $fileName]; // Return only the file name
        } else {
            return ["status" => "error", "message" => "Failed to upload: " . $file['name']];
        }
    }
    return ["status" => "error", "message" => "No file uploaded."];
}

// Main logic for handling the file upload and database insertion
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Sanitize input
        $documents_name = htmlspecialchars(trim($_POST['documents_name']));

        // Validate uploaded document
        $allowedMimeTypes = ["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", // .xlsx
                             "text/csv", // .csv
                             "application/vnd.openxmlformats-officedocument.wordprocessingml.document"]; // .docx

        $document = uploadFile($_FILES['user_document'], "../uploads/user_document/", $allowedMimeTypes);

        if ($document['status'] === "error") {
            throw new Exception($document['message']);
        }

        // Check if the document name and file already exist
        $sql = "SELECT COUNT(*) FROM user_document WHERE documents_name = ? AND user_document = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $documents_name, $document['file_name']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $count = $row[0];

        if ($count > 0) {
            throw new Exception("Document with the same name and file already exists.");
        }

        // Insert document details into the database
        $sql = "INSERT INTO user_document (documents_name, user_document) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $documents_name, $document['file_name']);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting document data: " . $stmt->error);
        }

        // Set success message
        $_SESSION['success'] = "Document successfully added!";
    } catch (Exception $e) {
        // Set error message
        $_SESSION['error'] = $e->getMessage();
    } finally {
        // Redirect back to the upload page
        header("Location: upload-document.php");
        exit();
    }
}
?>

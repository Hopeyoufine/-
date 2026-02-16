<?php
header('Content-Type: application/json');
require 'db.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $outputDir = __DIR__ . '/uploads/';
    $fileId = time(); // ใช้ timestamp ป้องกันชื่อไฟล์ซ้ำ
    $fileNameTemplate = "audio_" . $fileId;
    
    // 1. บันทึกข้อมูลเริ่มการทำงาน (สถานะ: กำลังโหลด)
    $stmt = $pdo->prepare("INSERT INTO conversions (video_url, status) VALUES (?, 'กำลังโหลด')");
    $stmt->execute([$url]);
    $last_id = $pdo->lastInsertId();

    // 2. คำสั่งเรียกใช้ yt-dlp ( -x คือเอาเฉพาะเสียง, --audio-format mp3 คือแปลงเป็น mp3 )
    $ytDlpPath = __DIR__ . '/yt-dlp.exe';
    // คำสั่งจะพยายามดึงชื่อคลิปวิดีโอมาเป็นชื่อไฟล์ด้วย
    $cmd = "\"$ytDlpPath\" -x --audio-format mp3 -o \"$outputDir$fileNameTemplate.%(ext)s\" \"$url\" 2>&1";

    // รันคำสั่ง
    exec($cmd, $output, $return_var);

    if ($return_var === 0) {
        // ถ้าสำเร็จ
        $finalFile = $fileNameTemplate . ".mp3";
        $fullPath = $outputDir . $finalFile;
        
        // คำนวณขนาดไฟล์จริง
        $sizeBytes = filesize($fullPath);
        $fileSizeText = round($sizeBytes / (1024 * 1024), 2) . " MB";

        // 3. อัปเดตข้อมูลในฐานข้อมูล (สถานะ: สำเร็จ)
        $update = $pdo->prepare("UPDATE conversions SET file_name = ?, file_size = ?, status = 'สำเร็จ' WHERE id = ?");
        $update->execute([$finalFile, $fileSizeText, $last_id]);

        echo json_encode(['status' => 'success', 'message' => 'แปลงไฟล์สำเร็จ']);
    } else {
        // ถ้าผิดพลาด
        $pdo->prepare("UPDATE conversions SET status = 'ล้มเหลว' WHERE id = ?")->execute([$last_id]);
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการโหลด']);
    }
}
?>
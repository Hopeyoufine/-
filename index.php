<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube MP3 Converter</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .youtube-icon { color: #FF0000; font-size: 4rem; margin-bottom: 1rem; }
        .btn-convert { background-color: #FF0000; color: white; border-radius: 10px; padding: 12px; font-weight: bold; transition: 0.3s; }
        .btn-convert:hover { background-color: #cc0000; color: white; transform: translateY(-2px); }
        .input-group-text { background-color: white; border-right: none; color: #e97509; }
        .form-control { border-left: none; }
        .form-control:focus { box-shadow: none; border-color: #dee2e6; }
        #loading { display: none; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card p-5 text-center">
                
                <div class="youtube-icon">
                    <i class="fab fa-youtube"></i>
                </div>
                
                <h2 class="fw-bold mb-2">YouTube <span class="text-secondary">to</span> MP3</h2>
                <p class="text-muted mb-4">วางลิงก์วิดีโอเพื่อแปลงเป็นไฟล์เสียงคุณภาพสูง</p>
                
                <form id="convertForm">
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                        <input type="url" class="form-control" id="videoUrl" name="url" placeholder="วางลิงก์ YouTube ที่นี่..." required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-convert">
                            <i class="fas fa-magic me-2"></i>เริ่มแปลงไฟล์
                        </button>
                    </div>
                </form>

                <div id="loading" class="mt-4">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2 text-secondary">กำลังประมวลผล...</p>
                </div>

                <div id="resultArea" class="mt-4" style="display:none;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>สำเร็จ! พร้อมดาวน์โหลด
                        <br>
                        <a href="#" id="downloadLink" class="btn btn-outline-success mt-3 w-100">
                            <i class="fas fa-download me-2"></i>Download MP3
                        </a>
                    </div>
                </div>

                <div id="errorArea" class="alert alert-danger mt-4" style="display:none;"></div>
                
            </div>
            
            <p class="text-center mt-4 text-secondary small">
                <i class="fas fa-shield-alt me-1"></i> ปลอดภัยและรวดเร็ว
            </p>
            <div class="row justify-content-center mt-5">
    <div class="mt-5">
    <h4 class="mb-3"><i class="fas fa-history me-2"></i>ประวัติการแปลงไฟล์</h4>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>วัน-เวลา</th>
                        <th>ชื่อไฟล์/URL</th>
                        <th>ขนาด</th>
                        <th>สถานะ</th>
                        <th>ดาวน์โหลด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require 'db.php';
                    // เรียงจากใหม่ล่าสุดไปเก่า
                    $stmt = $pdo->query("SELECT * FROM conversions ORDER BY created_at DESC");
                    while ($row = $stmt->fetch()):
                        // กำหนดสีของ Status
                        $badgeColor = 'bg-secondary';
                        if($row['status'] == 'สำเร็จ') $badgeColor = 'bg-success';
                        if($row['status'] == 'กำลังโหลด') $badgeColor = 'bg-warning text-dark';
                        if($row['status'] == 'ล้มเหลว') $badgeColor = 'bg-danger';
                    ?>
                    <tr>
                        <td><small><?php echo date('d/m/H:i', strtotime($row['created_at'])); ?></small></td>
                        <td><div class="text-truncate" style="max-width: 200px;"><?php echo $row['file_name'] ?? $row['video_url']; ?></div></td>
                        <td><?php echo $row['file_size'] ?? '-'; ?></td>
                        <td><span class="badge <?php echo $badgeColor; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <?php if($row['status'] == 'สำเร็จ'): ?>
                                <a href="uploads/<?php echo $row['file_name']; ?>" class="btn btn-sm btn-outline-primary" download>
                                    <i class="fas fa-download"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// (ใช้ Script เดิมที่คุณมีอยู่ได้เลยครับ แค่เปลี่ยน ID ให้ตรงกัน)
document.getElementById('convertForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const loading = document.getElementById('loading');
    const resultArea = document.getElementById('resultArea');
    const errorArea = document.getElementById('errorArea');
    
    loading.style.display = 'block';
    resultArea.style.display = 'none';
    errorArea.style.display = 'none';

    // จำลองการส่งข้อมูล (ให้คุณไปเชื่อมกับ process.php ต่อ)
    setTimeout(() => {
        loading.style.display = 'none';
        resultArea.style.display = 'block';
    }, 2000);
});
</script>

</body>
</html>
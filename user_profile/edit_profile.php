<?php
$page_title = "Edit Profil";
require_once __DIR__ . '/../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='".BASE_URL."/auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

$list_kampus = [
    "Universitas Singaperbangsa Karawang (UNSIKA)",
    "Universitas Buana Perjuangan (UBP)",
    "BSI Karawang (UBSI)",
    "STMIK Rosma",
    "STIKes Horizon Karawang",
    "STIE Pertiwi Karawang",
    "Politeknik Tri Mitra Karya Mandiri",
    "STIT Rakeyan Santang",
    "Lainnya / Umum"
];

if (isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $campus = trim($_POST['campus']); 
    $deskripsi = trim($_POST['description']);
    $password = $_POST['password']; 
    
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $msg = "Email sudah digunakan orang lain.";
        $msg_type = "danger";
    } else {
        $q_old = $conn->query("SELECT photo FROM users WHERE id = $user_id");
        $old_data = $q_old->fetch_assoc();
        $photo_name = $old_data['photo']; 

        if (!empty($_FILES['photo']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_name = "profile_" . $user_id . "_" . time() . "." . $ext;
                $target_dir = __DIR__ . "/../assets/images/profiles/"; 
                
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $new_name)) {
                    if ($old_data['photo'] && file_exists($target_dir . $old_data['photo'])) {
                        unlink($target_dir . $old_data['photo']); 
                    }
                    $photo_name = $new_name;
                } else {
                    $msg = "Gagal upload gambar.";
                    $msg_type = "warning";
                }
            } else {
                $msg = "Format foto harus JPG/PNG.";
                $msg_type = "warning";
            }
        }

        if (empty($msg) || $msg_type == "warning") { 
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name=?, email=?, campus=?, description=?, photo=?, password=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $email, $campus, $deskripsi, $photo_name, $hashed, $user_id);
            } else {
                $sql = "UPDATE users SET name=?, email=?, campus=?, description=?, photo=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $name, $email, $campus, $deskripsi, $photo_name, $user_id);
            }

            if ($stmt->execute()) {
                $_SESSION['name'] = $name; 
                echo "<script>alert('Profil berhasil diperbarui!'); window.location='profile.php';</script>";
                exit;
            } else {
                $msg = "Gagal update database.";
                $msg_type = "danger";
            }
        }
    }
}

$query = $conn->prepare("SELECT name, email, campus, role, photo, description FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-circle"></i> Edit Profil</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($msg): ?>
                        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                            <?= $msg ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <div class="mb-3 position-relative d-inline-block">
                                    <?php 
                                        if (!empty($user['photo'])) {
                                            $fotoPath = BASE_URL . "/assets/images/profiles/" . $user['photo'] . "?t=" . time();
                                        } else {
                                            $fotoPath = "https://via.placeholder.com/150?text=No+Photo";
                                        }
                                    ?>
                                    <img src="<?= $fotoPath ?>" 
                                         id="profilePreview"
                                         class="rounded-circle img-thumbnail shadow-sm" 
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         alt="Foto Profil">
                                </div>
                                <div class="mb-3">
                                    <label class="btn btn-sm btn-outline-primary w-100 cursor-pointer">
                                        <i class="bi bi-camera"></i> Ganti Foto
                                        <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                                    </label>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">Maks 2MB (JPG/PNG)</small>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Asal Kampus</label>
                                    <select name="campus" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Kampus --</option>
                                        <?php foreach($list_kampus as $kmp): ?>
                                            <option value="<?= $kmp ?>" <?= ($user['campus'] == $kmp) ? 'selected' : '' ?>><?= $kmp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Deskripsi Diri</label>
                                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                                </div>

                                <hr class="my-4">
                                <h6 class="text-muted mb-3"><i class="bi bi-shield-lock"></i> Ganti Password (Opsional)</h6>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengganti">
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <a href="profile.php" class="btn btn-light border px-4">Kembali</a>
                                    <button type="submit" name="update" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/edit_profile.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
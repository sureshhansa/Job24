<?php
/** @var array $user */
$resumeUrl = !empty($user['resume_file']) ? UPLOAD_URL . '/resumes/' . $user['resume_file'] : null;
?>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3"><?php require BASE_PATH . '/views/candidate/_nav.php'; ?></div>
        <div class="col-lg-9">
            <h1 class="h4 fw-bold mb-3">Profile &amp; Resume</h1>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Personal details</h2></div>
                        <div class="card-body">
                            <form action="<?= e(url('profile')) ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label">Full name</label>
                                    <input type="text" name="name" value="<?= e($user['name']) ?>" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" value="<?= e((string)$user['phone']) ?>" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="location" value="<?= e((string)$user['location']) ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Professional headline</label>
                                    <input type="text" name="headline" value="<?= e((string)$user['headline']) ?>" class="form-control" placeholder="e.g. Senior PHP Developer">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" value="<?= e($user['email']) ?>" class="form-control" disabled>
                                    <div class="form-text">Email cannot be changed.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Bio / Summary</label>
                                    <textarea name="bio" rows="4" class="form-control"><?= e((string)$user['bio']) ?></textarea>
                                </div>
                                <button class="btn btn-primary">Save changes</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white"><h2 class="h6 fw-bold mb-0">Resume</h2></div>
                        <div class="card-body">
                            <?php if ($resumeUrl): ?>
                                <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4"></i>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="small fw-semibold text-truncate">Current resume</div>
                                        <a href="<?= e($resumeUrl) ?>" target="_blank" class="small">View / Download</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small">No resume uploaded yet. Upload one to apply faster.</p>
                            <?php endif; ?>

                            <form action="<?= e(url('resume')) ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                                    <div class="form-text">PDF, DOC or DOCX. Max 3 MB.</div>
                                </div>
                                <button class="btn btn-outline-primary w-100"><i class="bi bi-upload"></i> Upload Resume</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

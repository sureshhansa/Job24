<?php /** @var string $sent */ ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>">Home</a></li>
                    <li class="breadcrumb-item active">Contact Us</li>
                </ol>
            </nav>

            <h1 class="fw-bold mb-2">Contact Us</h1>
            <p class="text-muted mb-5">Have a question or feedback? We'd love to hear from you.</p>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-primary fs-4"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <h6 class="fw-semibold mb-1">Email Us</h6>
                            <p class="text-muted small mb-0"><?= e(setting('contact_email', 'support@' . parse_url(BASE_URL, PHP_URL_HOST))) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-primary fs-4"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <h6 class="fw-semibold mb-1">Call Us</h6>
                            <p class="text-muted small mb-0"><?= e(setting('contact_phone', '315-35-9255')) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-primary fs-4"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <h6 class="fw-semibold mb-1">Address</h6>
                            <p class="text-muted small mb-0"><?= e(setting('contact_address', '400 W 41st St, Miami Beach, Florida, 33140, USA')) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($sent)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Thank you! Your message has been sent. We'll get back to you shortly.
                </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h5 class="fw-semibold mb-4">Send us a Message</h5>
                    <form method="post" action="<?= e(url('contact')) ?>">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Your Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="you@example.com" required maxlength="150">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" placeholder="How can we help?" required maxlength="200">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="6" placeholder="Tell us more..." required maxlength="2000"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

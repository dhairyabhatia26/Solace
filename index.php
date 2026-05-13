<?php
require_once __DIR__ . '/app/bootstrap.php';

if (isLoggedIn()) {
    redirectUserDashboard($_SESSION['user_role']);
}

require_once APP_PATH . '/layout/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center py-5">
        <div class="col-lg-6 mb-5 mb-lg-0 fade-in">
            <h1 class="display-3 fw-black page-title mb-4" style="font-size: 4rem; line-height: 1.1;">Find your <span class="text-primary">peace</span> in the academic rush.</h1>
            <p class="lead text-muted mb-5" style="font-size: 1.25rem;">Solace is an AI-powered student wellness platform designed to provide institutional support, resources, and a safe space for mental health awareness.</p>
            
            <div class="d-flex gap-3">
                <a href="<?php echo base_url('login.php'); ?>" class="btn btn-solace btn-lg px-5 py-3">Get Started</a>
                <a href="<?php echo base_url('register.php'); ?>" class="btn btn-solace-outline btn-lg px-5 py-3">Join Solace</a>
            </div>

            <div class="mt-5 pt-4 d-flex align-items-center gap-4 text-muted small fw-bold text-uppercase opacity-75">
                <span>AI-Assisted</span>
                <span class="border-start ps-4">Institutional Grade</span>
                <span class="border-start ps-4">Privacy First</span>
            </div>
        </div>
        
        <div class="col-lg-6 fade-in" style="animation-delay: 0.2s;">
            <div class="position-relative">
                <div class="bg-primary rounded-circle opacity-10 position-absolute" style="width: 500px; height: 500px; top: -50px; right: -50px; z-index: -1;"></div>
                <div class="card border-0 shadow-lg p-4" style="border-radius: 24px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                                  <path d="M8 14.933a.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067v13.866zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                                </svg>
                            </div>
                            <h5 class="mb-0 fw-bold">Safe & Secure</h5>
                        </div>
                        <p class="text-muted">Your wellness concerns are handled with institutional privacy standards and assigned to qualified counselors.</p>
                        <hr class="my-4 opacity-10">
                        <div class="row text-center g-4">
                            <div class="col-4">
                                <div class="stat-value" style="font-size: 1.5rem;">98%</div>
                                <div class="stat-label" style="font-size: 0.6rem;">Privacy Rate</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-value" style="font-size: 1.5rem;">AI</div>
                                <div class="stat-label" style="font-size: 0.6rem;">Insights</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-value" style="font-size: 1.5rem;">24/7</div>
                                <div class="stat-label" style="font-size: 0.6rem;">Resource Access</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/layout/footer.php'; ?>
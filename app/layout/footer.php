<?php
// includes/footer.php
$current_page = basename($_SERVER['PHP_SELF']);
$is_auth_page = in_array($current_page, ['login.php', 'register.php', 'index.php']);
?>

<?php if (!$is_auth_page && isLoggedIn()): ?>
        </div> <!-- End Container Fluid -->
    </div> <!-- End Content -->
<?php endif; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
</body>
</html>

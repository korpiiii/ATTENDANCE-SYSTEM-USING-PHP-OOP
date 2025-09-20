<?php
// templates/footer.php - Common footer for all pages
?>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Attendance System. All rights reserved.</p>
            <small class="text-muted">Version 1.0.0 |
                <?php
                if (isset($_SESSION['student_id'])) {
                    echo 'Logged in as: ' . $_SESSION['name'];
                } elseif (isset($_SESSION['admin_logged_in'])) {
                    echo 'Logged in as: ' . $_SESSION['admin_name'] . ' (' . $_SESSION['admin_role'] . ')';
                }
                ?>
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

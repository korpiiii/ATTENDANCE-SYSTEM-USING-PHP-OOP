<?php
// templates/login.php - Login form template
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0 text-center"><i class="fas fa-sign-in-alt me-2"></i>Login</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Login As</label>
                            <select class="form-select" name="role" required>
                                <option value="student" <?php echo (isset($role) && $role === 'student') ? 'selected' : ''; ?>>Student</option>
                                <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" id="identifier-label"><?php echo (isset($role) && $role === 'admin') ? 'Username' : 'Student ID'; ?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas <?php echo (isset($role) && $role === 'admin') ? 'fa-user-shield' : 'fa-id-card'; ?>"></i>
                                </span>
                                <input type="text" class="form-control" name="identifier"
                                       value="<?php echo isset($identifier) ? htmlspecialchars($identifier) : ''; ?>"
                                       placeholder="<?php echo (isset($role) && $role === 'admin') ? 'Enter username' : 'Enter student ID'; ?>"
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password"
                                       placeholder="Enter your password" required>
                                <button type="button" class="btn btn-outline-secondary password-toggle"
                                        onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>

                        <div class="text-center">
                            <p class="mb-2">
                                <a href="forgot-password.php" class="text-decoration-none">
                                    <i class="fas fa-key me-1"></i>Forgot Password?
                                </a>
                            </p>
                            <p class="mb-0">
                                Don't have an account?
                                <a href="register.php" class="text-decoration-none fw-semibold">
                                    <i class="fas fa-user-plus me-1"></i>Register here
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Update label based on role selection
document.querySelector('select[name="role"]').addEventListener('change', function() {
    const label = document.getElementById('identifier-label');
    const icon = document.querySelector('.input-group-text i');

    if (this.value === 'admin') {
        label.textContent = 'Username';
        icon.className = 'fas fa-user-shield';
        document.querySelector('input[name="identifier"]').placeholder = 'Enter username';
    } else {
        label.textContent = 'Student ID';
        icon.className = 'fas fa-id-card';
        document.querySelector('input[name="identifier"]').placeholder = 'Enter student ID';
    }
});
</script>

<?php
require_once '../config.php';
require_once '../includes/header.php';
?>

<h2>Login</h2>

<?php if (isset($_GET['error'])): ?>
    <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<form action="../processes/login_process.php" method="post">
    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div>
        <label for="role">Login as:</label>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <div>
        <button type="submit">Login</button>
    </div>
</form>

<p>Don't have an account? <a href="register.php">Register here</a></p>

<?php require_once '../includes/footer.php'; ?>

<?php
require_once 'config.php';

// Generate correct hash for admin123
$newHash = password_hash('admin123', PASSWORD_DEFAULT);

// Update the saint user password
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'saint@baps.org'");
$stmt->execute([$newHash]);

echo "<h3>Password Updated Successfully!</h3>";
echo "<p>New hash: " . $newHash . "</p>";
echo "<p>You can now login with:</p>";
echo "<ul>";
echo "<li><strong>Email:</strong> saint@baps.org</li>";
echo "<li><strong>Password:</strong> admin123</li>";
echo "</ul>";
echo "<p><a href='login.php'>Go to Login</a></p>";

// Verify it works
$result = password_verify('admin123', $newHash);
echo "<p>Verification test: " . ($result ? '✅ Hash is correct' : '❌ Hash failed') . "</p>";
?>

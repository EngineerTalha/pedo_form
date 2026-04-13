<?php
// view_applications.php
require_once 'config.php';
$user_id = $_SESSION['user_id'];
header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sun, 19 Nov 1978 05:00:00 GMT');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$sql = "SELECT * FROM applications,users WHERE applications.user_id = users.id  ORDER BY applications.submission_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Applications - PEDO</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1a5f7a; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .container { max-width: 1200px; margin: auto; }
        h1 { color: #1a5f7a; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PEDO Applications List</h1>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Eligibility</th><th>Total Experience</th><th>Submission Date</th><th>Photo</th></tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><input type="text" value="<?php echo htmlspecialchars($row['full_name']); ?>" name="full_name" class="form-control"></td>
                    <td><?php echo $row['eligibility']; ?></td>
                    <td><?php echo $row['total_experience']; ?></td>
                    <td><?php echo $row['submission_date']; ?></td>
                    <td>
                        <?php if($row['photo_path'] && file_exists($row['photo_path'])): ?>
                            <img src="<?php echo $row['photo_path']; ?>" width="50" height="60">
                        <?php else: ?>
                            No photo
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
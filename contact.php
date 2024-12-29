<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate input
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$name, $email, $subject, $message])) {
                $success = true;
                
                // Clear form data after successful submission
                $name = $email = $subject = $message = '';
                
                // TODO: Send email notification to admin
                // For now, we'll just store in database
            }
        } catch (PDOException $e) {
            $errors[] = "Failed to send message. Please try again later.";
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AI Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/contact.css">
</head>
<body class="contact-page">
    <?php include 'includes/header.php'; ?>

    <div class="contact-container">
        <div class="contact-content">
            <div class="contact-info">
                <h1>Get in Touch</h1>
                <p>Have questions about AI Fashion? We're here to help! Fill out the form below and we'll get back to you as soon as possible.</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-envelope"></i>
                        <h3>Email Us</h3>
                        <p>support@aifashion.com</p>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-clock"></i>
                        <h3>Response Time</h3>
                        <p>Within 24-48 hours</p>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Location</h3>
                        <p>Silicon Valley, CA</p>
                    </div>
                </div>
            </div>

            <div class="contact-form-container">
                <?php if ($success): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <p>Thank you for your message! We'll get back to you soon.</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" 
                                   placeholder="Your name"
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" 
                                   placeholder="Your email address"
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <div class="input-group">
                            <i class="fas fa-heading"></i>
                            <input type="text" id="subject" name="subject" 
                                   placeholder="What is this about?"
                                   value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <div class="input-group">
                            <i class="fas fa-comment"></i>
                            <textarea id="message" name="message" 
                                      placeholder="Your message here..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary btn-large">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

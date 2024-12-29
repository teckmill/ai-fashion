<?php
require_once '../includes/functions.php';
require_once '../includes/localization.php';

// Ensure user is admin
if (!isAuthenticated() || !isAdmin()) {
    header('Location: /login.php');
    exit;
}

$localization = Localization::getInstance();
$supportedLocales = $localization->getSupportedLocales();
$currentLocale = isset($_GET['locale']) ? $_GET['locale'] : 'en';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update':
            $translations = json_decode(file_get_contents("../lang/{$currentLocale}.json"), true);
            foreach ($_POST['translations'] as $key => $value) {
                $keys = explode('.', $key);
                $current = &$translations;
                foreach ($keys as $k) {
                    if (!isset($current[$k])) {
                        $current[$k] = [];
                    }
                    $current = &$current[$k];
                }
                $current = $value;
            }
            file_put_contents("../lang/{$currentLocale}.json", json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "Translations updated successfully!";
            break;
            
        case 'add_locale':
            $newLocale = $_POST['new_locale'];
            if (!empty($newLocale) && !isset($supportedLocales[$newLocale])) {
                // Copy English translations as template
                $enTranslations = file_get_contents("../lang/en.json");
                file_put_contents("../lang/{$newLocale}.json", $enTranslations);
                $message = "New locale added successfully!";
            }
            break;
    }
}

// Load current translations
$translations = json_decode(file_get_contents("../lang/{$currentLocale}.json"), true);

// Flatten translations for easier editing
function flattenTranslations($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, flattenTranslations($value, $prefix . $key . '.'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }
    return $result;
}

$flatTranslations = flattenTranslations($translations);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation Management</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Translation Management</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Locale Selection -->
        <div class="locale-selector">
            <h2>Select Locale</h2>
            <?php foreach ($supportedLocales as $code => $data): ?>
                <a href="?locale=<?php echo $code; ?>" 
                   class="locale-button <?php echo $code === $currentLocale ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($data['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Add New Locale -->
        <div class="add-locale">
            <h2>Add New Locale</h2>
            <form method="POST" class="add-locale-form">
                <input type="hidden" name="action" value="add_locale">
                <input type="text" name="new_locale" placeholder="Locale code (e.g., de)" required>
                <button type="submit">Add Locale</button>
            </form>
        </div>

        <!-- Edit Translations -->
        <div class="translations-editor">
            <h2>Edit Translations for <?php echo htmlspecialchars($supportedLocales[$currentLocale]['name']); ?></h2>
            <form method="POST" class="translations-form">
                <input type="hidden" name="action" value="update">
                <?php foreach ($flatTranslations as $key => $value): ?>
                    <div class="translation-item">
                        <label for="<?php echo htmlspecialchars($key); ?>">
                            <?php echo htmlspecialchars($key); ?>:
                        </label>
                        <input type="text" 
                               name="translations[<?php echo htmlspecialchars($key); ?>]" 
                               id="<?php echo htmlspecialchars($key); ?>"
                               value="<?php echo htmlspecialchars($value); ?>"
                               required>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="save-button">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Add confirmation before leaving page with unsaved changes
        const form = document.querySelector('.translations-form');
        let formChanged = false;

        form.addEventListener('input', () => {
            formChanged = true;
        });

        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        form.addEventListener('submit', () => {
            formChanged = false;
        });
    </script>
</body>
</html>

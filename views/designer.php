<?php
require_once './includes/config.php';
require_once './includes/auth.php';

$auth = new Auth($pdo);
$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: ./auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Fashion Designer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/recommendations.css">
</head>
<body>
    <?php include './includes/header.php'; ?>

    <div class="container">
        <header class="header">
            <h1>AI Fashion Designer</h1>
            <p>Experience the future of personal styling with our cutting-edge AI technology. Create your perfect look with precision and style.</p>
        </header>

        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%"></div>
            </div>
        </div>

        <div class="form-container">
            <div class="form-step active" id="step1">
                <h2>Your Style Profile</h2>
                <p class="step-description">Let's start by understanding your personal style preferences.</p>
                
                <div class="style-cards">
                    <div class="style-card">
                        <i class="fas fa-tshirt"></i>
                        <h3>Minimalist</h3>
                        <p>Clean lines, neutral colors, and timeless pieces.</p>
                    </div>
                    <div class="style-card">
                        <i class="fas fa-palette"></i>
                        <h3>Expressive</h3>
                        <p>Bold patterns, vibrant colors, and unique designs.</p>
                    </div>
                    <div class="style-card">
                        <i class="fas fa-crown"></i>
                        <h3>Classic</h3>
                        <p>Traditional cuts, refined details, and elegant style.</p>
                    </div>
                    <div class="style-card">
                        <i class="fas fa-star"></i>
                        <h3>Trendy</h3>
                        <p>Latest fashion, modern looks, and contemporary pieces.</p>
                    </div>
                </div>

                <div class="measurements-section">
                    <div class="form-group height-group">
                        <label>Height*</label>
                        <div class="height-inputs">
                            <div class="input-with-label">
                                <input type="number" id="heightFeet" name="heightFeet" min="4" max="7" placeholder="5" required>
                                <span>ft</span>
                            </div>
                            <div class="input-with-label">
                                <input type="number" id="heightInches" name="heightInches" min="0" max="11" placeholder="8" required>
                                <span>in</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="weight">Weight*</label>
                        <div class="input-with-label">
                            <input type="number" id="weight" name="weight" min="80" max="400" placeholder="150" required>
                            <span>lbs</span>
                        </div>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn-next">Continue</button>
                </div>
            </div>

            <div class="form-step" id="step2">
                <h2>Occasion & Preferences</h2>
                <p class="step-description">Tell us about the occasion and your specific preferences.</p>

                <div class="occasion-cards">
                    <div class="occasion-card">
                        <i class="fas fa-briefcase"></i>
                        <h3>Business</h3>
                        <p>Professional attire for work and meetings.</p>
                    </div>
                    <div class="occasion-card">
                        <i class="fas fa-glass-cheers"></i>
                        <h3>Party</h3>
                        <p>Stylish outfits for social events and celebrations.</p>
                    </div>
                    <div class="occasion-card">
                        <i class="fas fa-coffee"></i>
                        <h3>Casual</h3>
                        <p>Comfortable yet stylish everyday wear.</p>
                    </div>
                    <div class="occasion-card">
                        <i class="fas fa-dumbbell"></i>
                        <h3>Active</h3>
                        <p>Functional clothing for sports and workouts.</p>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Continue</button>
                </div>
            </div>

            <div class="form-step" id="step3">
                <h2>Color & Pattern Preferences</h2>
                <p class="step-description">Select your preferred colors and patterns.</p>

                <div class="color-preferences">
                    <div class="color-group">
                        <h3>Base Colors</h3>
                        <div class="color-options">
                            <div class="color-option" style="background-color: #000000;" data-color="black"></div>
                            <div class="color-option" style="background-color: #FFFFFF; border: 1px solid #ccc;" data-color="white"></div>
                            <div class="color-option" style="background-color: #808080;" data-color="gray"></div>
                            <div class="color-option" style="background-color: #964B00;" data-color="brown"></div>
                        </div>
                    </div>

                    <div class="color-group">
                        <h3>Accent Colors</h3>
                        <div class="color-options">
                            <div class="color-option" style="background-color: #FF0000;" data-color="red"></div>
                            <div class="color-option" style="background-color: #0000FF;" data-color="blue"></div>
                            <div class="color-option" style="background-color: #008000;" data-color="green"></div>
                            <div class="color-option" style="background-color: #FFD700;" data-color="yellow"></div>
                        </div>
                    </div>
                </div>

                <div class="pattern-preferences">
                    <h3>Patterns</h3>
                    <div class="pattern-options">
                        <div class="pattern-option" data-pattern="solid">
                            <img src="./images/patterns/solid.png" alt="Solid">
                            <span>Solid</span>
                        </div>
                        <div class="pattern-option" data-pattern="striped">
                            <img src="./images/patterns/striped.png" alt="Striped">
                            <span>Striped</span>
                        </div>
                        <div class="pattern-option" data-pattern="floral">
                            <img src="./images/patterns/floral.png" alt="Floral">
                            <span>Floral</span>
                        </div>
                        <div class="pattern-option" data-pattern="checkered">
                            <img src="./images/patterns/checkered.png" alt="Checkered">
                            <span>Checkered</span>
                        </div>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-generate">Generate Outfit</button>
                </div>
            </div>
        </div>

        <div class="results" style="display: none;">
            <h2>Your Personalized Outfit</h2>
            <div class="outfit-suggestion">
                <div class="outfit-image">
                    <!-- AI-generated outfit image will be displayed here -->
                </div>
                <div class="outfit-details">
                    <h3>Outfit Components</h3>
                    <ul class="outfit-items">
                        <!-- Dynamically populated outfit items -->
                    </ul>
                    <div class="outfit-actions">
                        <button class="btn-save">Save Outfit</button>
                        <button class="btn-share">Share</button>
                        <button class="btn-mix-match">Mix & Match</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mix-match" style="display: none;">
            <h2>Mix & Match</h2>
            <div class="outfit-builder">
                <div class="outfit-slots">
                    <div class="outfit-slot" data-type="tops">
                        <h3>Tops</h3>
                        <div class="slot-content"></div>
                    </div>
                    <div class="outfit-slot" data-type="bottoms">
                        <h3>Bottoms</h3>
                        <div class="slot-content"></div>
                    </div>
                    <div class="outfit-slot" data-type="shoes">
                        <h3>Shoes</h3>
                        <div class="slot-content"></div>
                    </div>
                    <div class="outfit-slot" data-type="accessories">
                        <h3>Accessories</h3>
                        <div class="slot-content"></div>
                    </div>
                </div>
                <div class="outfit-preview">
                    <h3>Preview</h3>
                    <div class="preview-content">
                        <!-- Preview of the mixed and matched outfit -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>

    <!-- Weather Integration Script -->
    <script src="https://api.weatherapi.com/v1/current.json"></script>
    <script src="./js/fashion-ai.js"></script>
</body>
</html>

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get all necessary elements
    const formSteps = document.querySelectorAll('.form-step');
    const progressBar = document.querySelector('.progress-bar');
    const nextButton = document.querySelector('.next-button');
    const prevButton = document.querySelector('.prev-button');
    const submitButton = document.querySelector('.submit-button');
    const styleCards = document.querySelectorAll('.style-card');
    const occasionCards = document.querySelectorAll('.occasion-card');
    const resultsSection = document.querySelector('.results');

    let currentStep = 0;

    // Form data object to store all selections
    const formData = {
        style: '',
        occasion: '',
        heightFeet: '',
        heightInches: '',
        weight: '',
        season: '',
        colorPreference: ''
    };

    // Update progress bar
    function updateProgressBar() {
        const progress = ((currentStep + 1) / formSteps.length) * 100;
        progressBar.style.width = `${progress}%`;
    }

    // Show error message
    function showError(message) {
        const errorDiv = document.querySelector('.error-message') || document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.color = 'var(--accent-1)';
        errorDiv.style.marginTop = '1rem';
        errorDiv.style.fontSize = '0.875rem';
        
        const currentStepElement = formSteps[currentStep];
        if (!currentStepElement.querySelector('.error-message')) {
            currentStepElement.appendChild(errorDiv);
        } else {
            errorDiv.textContent = message;
        }

        // Remove error message after 3 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 3000);
    }

    // Navigation functions
    function showStep(step) {
        formSteps.forEach((formStep, index) => {
            formStep.classList.toggle('active', index === step);
        });
        
        // Update button visibility
        prevButton.style.display = step === 0 ? 'none' : 'block';
        nextButton.style.display = step === formSteps.length - 1 ? 'none' : 'block';
        submitButton.style.display = step === formSteps.length - 1 ? 'block' : 'none';
        
        updateProgressBar();
    }

    function nextStep() {
        if (validateStep(currentStep)) {
            if (currentStep < formSteps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }
    }

    function prevStep() {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    }

    // Add click event listeners for navigation
    if (nextButton) {
        nextButton.addEventListener('click', nextStep);
    }
    if (prevButton) {
        prevButton.addEventListener('click', prevStep);
    }
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateStep(currentStep)) {
                generateOutfit();
            }
        });
    }

    // Style card selection
    styleCards.forEach(card => {
        card.addEventListener('click', function() {
            styleCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            formData.style = this.querySelector('h3').textContent;
        });
    });

    // Occasion card selection
    occasionCards.forEach(card => {
        card.addEventListener('click', function() {
            occasionCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            formData.occasion = this.querySelector('h3').textContent;
        });
    });

    // Add input event listeners for measurements
    const heightFeetInput = document.getElementById('heightFeet');
    const heightInchesInput = document.getElementById('heightInches');
    const weightInput = document.getElementById('weight');
    const seasonSelect = document.getElementById('season');
    const colorPreferenceSelect = document.getElementById('colorPreference');

    heightFeetInput.addEventListener('input', function() {
        formData.heightFeet = this.value;
        this.classList.toggle('error', this.value && (this.value < 4 || this.value > 7));
    });

    heightInchesInput.addEventListener('input', function() {
        formData.heightInches = this.value;
        this.classList.toggle('error', this.value && (this.value < 0 || this.value > 11));
    });

    weightInput.addEventListener('input', function() {
        formData.weight = this.value;
        this.classList.toggle('error', this.value && (this.value < 80 || this.value > 400));
    });

    seasonSelect.addEventListener('change', function() {
        formData.season = this.value;
    });

    colorPreferenceSelect.addEventListener('change', function() {
        formData.colorPreference = this.value;
    });

    // Form validation
    function validateStep(step) {
        let isValid = true;

        if (step === 0) {
            // Validate height
            if (!heightFeetInput.value || heightFeetInput.value < 4 || heightFeetInput.value > 7) {
                isValid = false;
                heightFeetInput.classList.add('error');
                showError('Please enter a valid height (4-7 feet)');
            }

            if (!heightInchesInput.value || heightInchesInput.value < 0 || heightInchesInput.value > 11) {
                isValid = false;
                heightInchesInput.classList.add('error');
                showError('Please enter valid inches (0-11)');
            }

            if (!weightInput.value || weightInput.value < 80 || weightInput.value > 400) {
                isValid = false;
                weightInput.classList.add('error');
                showError('Please enter a valid weight (80-400 lbs)');
            }

            // Validate style selection
            if (!formData.style) {
                isValid = false;
                showError('Please select a style preference');
            }
        } else if (step === 1) {
            // Validate occasion selection
            if (!formData.occasion) {
                isValid = false;
                showError('Please select an occasion');
            }

            // Validate season selection
            if (!seasonSelect.value) {
                isValid = false;
                seasonSelect.classList.add('error');
                showError('Please select a season');
            }

            // Validate color preference
            if (!colorPreferenceSelect.value) {
                isValid = false;
                colorPreferenceSelect.classList.add('error');
                showError('Please select a color preference');
            }
        }

        return isValid;
    }

    // Initialize the form
    showStep(currentStep);

    // Function to generate outfit
    function generateOutfit() {
        if (!resultsSection) return;

        // Create outfit recommendation based on form data
        const outfitHtml = `
            <h2>Your Personalized Outfit Recommendation</h2>
            <div class="outfit-details">
                <h3>Based on Your Preferences:</h3>
                <ul>
                    <li>Style: ${formData.style}</li>
                    <li>Occasion: ${formData.occasion}</li>
                    <li>Height: ${formData.heightFeet}'${formData.heightInches}"</li>
                    <li>Weight: ${formData.weight} lbs</li>
                    <li>Season: ${formData.season}</li>
                    <li>Color Preference: ${formData.colorPreference}</li>
                </ul>
                <div class="outfit-recommendation">
                    <h3>Recommended Outfit:</h3>
                    <p>Here's a personalized outfit that matches your style and preferences...</p>
                    <!-- Add more detailed outfit recommendations here -->
                </div>
                <div class="styling-tips">
                    <h3>Styling Tips:</h3>
                    <ul>
                        <li>Consider layering pieces for versatility</li>
                        <li>Accessorize with complementary colors</li>
                        <li>Choose fabrics appropriate for ${formData.season}</li>
                    </ul>
                </div>
            </div>
        `;

        resultsSection.innerHTML = outfitHtml;
        resultsSection.style.display = 'block';
    }
});

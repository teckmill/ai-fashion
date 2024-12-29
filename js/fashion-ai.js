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

    // Function to show loading animation
    function showLoading() {
        const loadingContainer = document.querySelector('.loading-container');
        loadingContainer.style.display = 'flex';
    }

    function hideLoading() {
        const loadingContainer = document.querySelector('.loading-container');
        loadingContainer.style.display = 'none';
    }

    // Function to save outfit to history
    function saveOutfit(outfitData) {
        let outfits = JSON.parse(localStorage.getItem('savedOutfits') || '[]');
        outfitData.date = new Date().toISOString();
        outfits.push(outfitData);
        localStorage.setItem('savedOutfits', JSON.stringify(outfits));
        updateOutfitHistory();
    }

    // Function to update outfit history display
    function updateOutfitHistory() {
        const historyGrid = document.querySelector('.history-grid');
        const outfits = JSON.parse(localStorage.getItem('savedOutfits') || '[]');
        
        historyGrid.innerHTML = outfits.reverse().slice(0, 6).map(outfit => `
            <div class="history-item" onclick="loadSavedOutfit(${JSON.stringify(outfit).replace(/"/g, '&quot;')})">
                <div class="history-date">${new Date(outfit.date).toLocaleDateString()}</div>
                <h3>${outfit.style} for ${outfit.occasion}</h3>
                <p>Season: ${outfit.season}</p>
                <p>Color Scheme: ${outfit.colorPreference}</p>
            </div>
        `).join('');
    }

    // Function to load saved outfit
    function loadSavedOutfit(outfit) {
        formData = { ...outfit };
        generateOutfit();
    }

    // Function to get weather data
    async function getWeatherData() {
        try {
            const response = await fetch('https://api.weatherapi.com/v1/current.json?key=YOUR_API_KEY&q=auto:ip');
            const data = await response.json();
            return {
                temp: data.current.temp_c,
                condition: data.current.condition.text,
                icon: data.current.condition.icon
            };
        } catch (error) {
            console.error('Error fetching weather:', error);
            return null;
        }
    }

    // Function to generate weather-appropriate recommendations
    function getWeatherBasedRecommendations(weather) {
        const temp = weather.temp;
        let recommendations = [];

        if (temp < 10) {
            recommendations.push(
                'Layer up with warm, insulating pieces',
                'Consider a heavy coat or jacket',
                'Don\'t forget winter accessories like scarves and gloves'
            );
        } else if (temp < 20) {
            recommendations.push(
                'A light jacket or sweater would be appropriate',
                'Consider layering for temperature changes',
                'Light scarves can add both style and warmth'
            );
        } else {
            recommendations.push(
                'Choose breathable, lightweight fabrics',
                'Consider UV protection in your outfit choices',
                'Light colors reflect heat better'
            );
        }

        return recommendations;
    }

    // Enhance generateOutfit function
    async function generateOutfit() {
        showLoading();

        // Get weather data
        const weather = await getWeatherData();
        
        // Generate outfit after a slight delay for loading animation
        setTimeout(async () => {
            const resultsSection = document.querySelector('.results');
            if (!resultsSection) {
                hideLoading();
                return;
            }

            // Get all the outfit components as before...
            const heightInTotal = (parseInt(formData.heightFeet) * 12) + parseInt(formData.heightInches);
            const weight = parseInt(formData.weight);
            const bodyType = determineBodyType(heightInTotal, weight);
            const fabricSuggestions = getSeasonalFabrics(formData.season);
            const colorScheme = getColorScheme(formData.colorPreference, formData.season);
            const outfitPieces = getOutfitPieces(formData.style, formData.occasion, bodyType, formData.season);
            const stylingTips = getStylingTips(bodyType, formData.style, formData.season);

            // Add weather-based recommendations if available
            let weatherHtml = '';
            if (weather) {
                const weatherTips = getWeatherBasedRecommendations(weather);
                weatherHtml = `
                    <div class="weather-info">
                        <div class="weather-icon">
                            <i class="fas ${weather.temp < 10 ? 'fa-snowflake' : weather.temp > 20 ? 'fa-sun' : 'fa-cloud'}"></i>
                        </div>
                        <div class="weather-details">
                            <div class="weather-temp">${weather.temp}°C</div>
                            <div class="weather-desc">${weather.condition}</div>
                        </div>
                    </div>
                `;
            }

            // Generate the outfit HTML as before...
            const outfitHtml = `
                <h2>Your Personalized Outfit Recommendation</h2>
                ${weatherHtml}
                <div class="outfit-details">
                    <div class="outfit-summary">
                        <h3>Your Style Profile</h3>
                        <ul class="profile-details">
                            <li><strong>Style Preference:</strong> ${formData.style}</li>
                            <li><strong>Occasion:</strong> ${formData.occasion}</li>
                            <li><strong>Body Type:</strong> ${bodyType.name}</li>
                            <li><strong>Season:</strong> ${formData.season}</li>
                            <li><strong>Color Palette:</strong> ${formData.colorPreference}</li>
                        </ul>
                    </div>

                    <div class="outfit-recommendation">
                        <h3>Recommended Outfit</h3>
                        <div class="outfit-pieces">
                            ${outfitPieces.map(piece => `
                                <div class="outfit-piece">
                                    <i class="${piece.icon}"></i>
                                    <h4>${piece.name}</h4>
                                    <p>${piece.description}</p>
                                    <div class="piece-details">
                                        <span class="fabric">Fabric: ${piece.fabric}</span>
                                        <span class="color">Color: ${piece.color}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="styling-guide">
                        <div class="color-palette">
                            <h3>Recommended Color Palette</h3>
                            <div class="color-chips">
                                ${colorScheme.map(color => `
                                    <div class="color-chip" style="background-color: ${color.hex}">
                                        <span>${color.name}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <div class="fabric-suggestions">
                            <h3>Recommended Fabrics for ${formData.season}</h3>
                            <ul>
                                ${fabricSuggestions.map(fabric => `
                                    <li>
                                        <strong>${fabric.name}:</strong> ${fabric.description}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>

                        <div class="styling-tips">
                            <h3>Personalized Styling Tips</h3>
                            <ul>
                                ${stylingTips.map(tip => `<li>${tip}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="action-buttons">
                    <button class="save-outfit" onclick="saveOutfit(${JSON.stringify(formData)})">
                        <i class="fas fa-save"></i>
                        Save Outfit
                    </button>
                    <div class="share-buttons">
                        <button class="share-button" onclick="shareOutfit('twitter')">
                            <i class="fab fa-twitter"></i>
                            Tweet
                        </button>
                        <button class="share-button" onclick="shareOutfit('pinterest')">
                            <i class="fab fa-pinterest"></i>
                            Pin
                        </button>
                    </div>
                </div>
            `;

            resultsSection.innerHTML = outfitHtml;
            resultsSection.style.display = 'block';
            hideLoading();
            
            // Smooth scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }, 1500);
    }

    // Function to share outfit
    function shareOutfit(platform) {
        const outfitDesc = `Check out my ${formData.style} outfit for ${formData.occasion} from AI Fashion Designer!`;
        const url = window.location.href;

        switch(platform) {
            case 'twitter':
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(outfitDesc)}&url=${encodeURIComponent(url)}`);
                break;
            case 'pinterest':
                window.open(`https://pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}&description=${encodeURIComponent(outfitDesc)}`);
                break;
        }
    }

    // Helper function to determine body type
    function determineBodyType(heightInches, weight) {
        const heightInMeters = heightInches * 0.0254;
        const weightInKg = weight * 0.453592;
        const bmi = weightInKg / (heightInMeters * heightInMeters);

        // Basic body type determination
        if (heightInches < 67) { // Under 5'7"
            return {
                name: 'Petite',
                features: ['Shorter frame', 'Proportional build'],
                recommendations: ['Vertical lines', 'High-waisted pieces', 'Monochromatic looks']
            };
        } else if (heightInches > 72) { // Over 6'
            return {
                name: 'Tall',
                features: ['Long frame', 'Extended proportions'],
                recommendations: ['Layered looks', 'Break up height with contrasting colors', 'Long lines']
            };
        } else {
            return {
                name: 'Average',
                features: ['Medium frame', 'Balanced proportions'],
                recommendations: ['Versatile styles', 'Classic cuts', 'Balanced proportions']
            };
        }
    }

    // Helper function to get seasonal fabric recommendations
    function getSeasonalFabrics(season) {
        const fabricsBySeasons = {
            spring: [
                { name: 'Cotton', description: 'Light and breathable for warming temperatures' },
                { name: 'Linen', description: 'Natural fiber perfect for layering' },
                { name: 'Light Wool', description: 'Good for spring evenings' }
            ],
            summer: [
                { name: 'Linen', description: 'Keeps you cool and looks effortlessly stylish' },
                { name: 'Cotton', description: 'Breathable and perfect for hot days' },
                { name: 'Chambray', description: 'Light and comfortable denim alternative' }
            ],
            fall: [
                { name: 'Wool', description: 'Warm and perfect for layering' },
                { name: 'Cashmere', description: 'Luxurious and warm without bulk' },
                { name: 'Corduroy', description: 'Durable and seasonally appropriate' }
            ],
            winter: [
                { name: 'Wool', description: 'Warm and insulating' },
                { name: 'Cashmere', description: 'Luxury warmth for cold days' },
                { name: 'Fleece', description: 'Synthetic warmth for casual wear' }
            ]
        };
        return fabricsBySeasons[season.toLowerCase()] || fabricsBySeasons.fall;
    }

    // Helper function to get color scheme
    function getColorScheme(preference, season) {
        const colorSchemes = {
            warm: [
                { name: 'Burgundy', hex: '#800020' },
                { name: 'Rust', hex: '#B7410E' },
                { name: 'Warm Brown', hex: '#964B00' },
                { name: 'Golden Yellow', hex: '#FFD700' }
            ],
            cool: [
                { name: 'Navy', hex: '#000080' },
                { name: 'Forest Green', hex: '#228B22' },
                { name: 'Deep Purple', hex: '#483D8B' },
                { name: 'Steel Blue', hex: '#4682B4' }
            ],
            neutral: [
                { name: 'Charcoal', hex: '#36454F' },
                { name: 'Taupe', hex: '#483C32' },
                { name: 'Cream', hex: '#FFFDD0' },
                { name: 'Gray', hex: '#808080' }
            ],
            bright: [
                { name: 'Vibrant Red', hex: '#FF0000' },
                { name: 'Electric Blue', hex: '#0000FF' },
                { name: 'Emerald', hex: '#50C878' },
                { name: 'Royal Purple', hex: '#7851A9' }
            ],
            pastel: [
                { name: 'Soft Pink', hex: '#FFB6C1' },
                { name: 'Baby Blue', hex: '#89CFF0' },
                { name: 'Mint', hex: '#98FF98' },
                { name: 'Lavender', hex: '#E6E6FA' }
            ]
        };
        return colorSchemes[preference.toLowerCase()] || colorSchemes.neutral;
    }

    // Helper function to get outfit pieces
    function getOutfitPieces(style, occasion, bodyType, season) {
        const pieces = [];
        
        // Base outfit structure based on occasion
        switch(occasion.toLowerCase()) {
            case 'formal':
                pieces.push(
                    {
                        name: 'Blazer',
                        icon: 'fas fa-user-tie',
                        description: `Tailored fit ${style.toLowerCase()} blazer that complements your ${bodyType.name.toLowerCase()} frame`,
                        fabric: getSeasonalFabrics(season)[0].name,
                        color: getColorScheme(style, season)[0].name
                    },
                    {
                        name: 'Dress Shirt',
                        icon: 'fas fa-tshirt',
                        description: 'Crisp, well-fitted dress shirt',
                        fabric: 'Cotton Blend',
                        color: 'Classic White'
                    },
                    {
                        name: 'Dress Pants',
                        icon: 'fas fa-male',
                        description: 'Tailored dress pants with perfect break',
                        fabric: getSeasonalFabrics(season)[0].name,
                        color: getColorScheme(style, season)[1].name
                    }
                );
                break;
            case 'casual':
                pieces.push(
                    {
                        name: 'Top',
                        icon: 'fas fa-tshirt',
                        description: `Comfortable ${style.toLowerCase()} style top`,
                        fabric: getSeasonalFabrics(season)[1].name,
                        color: getColorScheme(style, season)[0].name
                    },
                    {
                        name: 'Bottom',
                        icon: 'fas fa-male',
                        description: 'Well-fitted casual pants',
                        fabric: 'Premium Denim',
                        color: getColorScheme(style, season)[2].name
                    }
                );
                break;
            default:
                pieces.push(
                    {
                        name: 'Versatile Top',
                        icon: 'fas fa-tshirt',
                        description: `${style} inspired top that can be dressed up or down`,
                        fabric: getSeasonalFabrics(season)[0].name,
                        color: getColorScheme(style, season)[0].name
                    },
                    {
                        name: 'Smart Casual Bottom',
                        icon: 'fas fa-male',
                        description: 'Adaptable bottom piece for various occasions',
                        fabric: getSeasonalFabrics(season)[1].name,
                        color: getColorScheme(style, season)[1].name
                    }
                );
        }

        // Add accessories based on style
        pieces.push({
            name: 'Accessories',
            icon: 'fas fa-gem',
            description: `${style}-inspired accessories to complete the look`,
            fabric: 'Mixed Materials',
            color: getColorScheme(style, season)[3].name
        });

        return pieces;
    }

    // Helper function to get styling tips
    function getStylingTips(bodyType, style, season) {
        const generalTips = [
            `Emphasize your ${bodyType.name.toLowerCase()} frame with ${bodyType.recommendations[0].toLowerCase()}`,
            `Layer appropriately for ${season} while maintaining your ${style.toLowerCase()} aesthetic`,
            'Mix textures to add depth to your outfit',
            'Accessorize thoughtfully to elevate the look',
            `Consider ${bodyType.recommendations[1].toLowerCase()} to enhance your proportions`
        ];

        const seasonalTips = {
            spring: 'Incorporate light layers that can be removed as the day warms up',
            summer: 'Choose breathable fabrics and looser fits for comfort',
            fall: 'Layer pieces for both style and warmth',
            winter: 'Focus on warm, insulating pieces while maintaining style'
        };

        const styleTips = {
            classic: 'Invest in timeless pieces that never go out of style',
            trendy: 'Mix current trends with classic basics',
            casual: 'Focus on comfort while maintaining a put-together look',
            elegant: 'Pay attention to fit and fabric quality',
            creative: 'Express yourself through unique combinations and statement pieces'
        };

        return [
            ...generalTips,
            seasonalTips[season.toLowerCase()],
            styleTips[style.toLowerCase()] || 'Create a balanced look that reflects your personal style'
        ];
    }

    // Initialize history on load
    updateOutfitHistory();

    // Virtual Wardrobe
    const wardrobe = {
        items: JSON.parse(localStorage.getItem('wardrobeItems') || '[]'),

        addItem(item) {
            this.items.push(item);
            this.saveItems();
            this.renderItems();
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.saveItems();
            this.renderItems();
        },

        saveItems() {
            localStorage.setItem('wardrobeItems', JSON.stringify(this.items));
        },

        filterItems(category = 'all', season = 'all') {
            return this.items.filter(item => {
                const categoryMatch = category === 'all' || item.category === category;
                const seasonMatch = season === 'all' || item.seasons.includes(season);
                return categoryMatch && seasonMatch;
            });
        },

        renderItems() {
            const grid = document.querySelector('.wardrobe-grid');
            const category = document.getElementById('categoryFilter').value;
            const season = document.getElementById('seasonFilter').value;
            const filteredItems = this.filterItems(category, season);

            grid.innerHTML = filteredItems.map((item, index) => `
                <div class="wardrobe-item">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                    <h4>${item.name}</h4>
                    <p>${item.category}</p>
                    <div class="item-seasons">
                        ${item.seasons.map(s => `<span>${s}</span>`).join('')}
                    </div>
                    <button onclick="wardrobe.removeItem(${index})">Remove</button>
                </div>
            `).join('');
        }
    };

    // Mix & Match Feature
    const mixMatch = {
        selectedItems: {
            tops: null,
            bottoms: null,
            shoes: null,
            accessories: null
        },

        selectItem(type, item) {
            this.selectedItems[type] = item;
            this.updatePreview();
        },

        updatePreview() {
            const preview = document.querySelector('.preview-container');
            preview.innerHTML = Object.entries(this.selectedItems)
                .filter(([_, item]) => item)
                .map(([type, item]) => `
                    <div class="selected-item ${type}">
                        ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                        <p>${item.name}</p>
                    </div>
                `).join('');
        }
    };

    // Style Quiz
    const styleQuiz = {
        questions: [
            {
                text: "Which colors do you feel most confident wearing?",
                options: ["Neutrals", "Bold & Bright", "Pastels", "Earth Tones"]
            },
            {
                text: "How would you describe your typical daily style?",
                options: ["Casual", "Professional", "Trendy", "Classic"]
            },
            {
                text: "What's your preferred fit for clothing?",
                options: ["Fitted", "Loose & Flowy", "Mix of Both", "Structured"]
            },
            {
                text: "Which decade's fashion inspires you the most?",
                options: ["90s", "2000s", "2010s", "Timeless"]
            }
        ],
        currentQuestion: 0,
        answers: [],

        start() {
            this.currentQuestion = 0;
            this.answers = [];
            this.renderQuestion();
        },

        renderQuestion() {
            const container = document.querySelector('.quiz-container');
            const question = this.questions[this.currentQuestion];
            const progress = ((this.currentQuestion + 1) / this.questions.length) * 100;

            document.querySelector('.progress-bar').style.width = `${progress}%`;

            container.innerHTML = `
                <h3>${question.text}</h3>
                <div class="options-grid">
                    ${question.options.map((option, index) => `
                        <button onclick="styleQuiz.selectAnswer(${index})" class="quiz-option">
                            ${option}
                        </button>
                    `).join('')}
                </div>
            `;
        },

        selectAnswer(index) {
            this.answers.push(this.questions[this.currentQuestion].options[index]);
            
            if (this.currentQuestion < this.questions.length - 1) {
                this.currentQuestion++;
                this.renderQuestion();
            } else {
                this.showResults();
            }
        },

        showResults() {
            const styleProfile = this.analyzeAnswers();
            const container = document.querySelector('.quiz-container');
            
            container.innerHTML = `
                <h3>Your Style Profile</h3>
                <div class="quiz-results">
                    <p>Based on your answers, your style personality is: <strong>${styleProfile.personality}</strong></p>
                    <p>${styleProfile.description}</p>
                    <div class="style-recommendations">
                        <h4>Recommended Pieces:</h4>
                        <ul>
                            ${styleProfile.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
        },

        analyzeAnswers() {
            // Simple analysis based on answers
            const personalities = {
                'Trendsetter': {
                    description: 'You love staying ahead of fashion trends and aren\'t afraid to make bold choices.',
                    recommendations: [
                        'Statement pieces in current trends',
                        'Bold accessories',
                        'Mix of classic and contemporary items'
                    ]
                },
                'Classic Minimalist': {
                    description: 'You appreciate timeless pieces and clean, sophisticated looks.',
                    recommendations: [
                        'High-quality basics',
                        'Neutral color palette',
                        'Structured blazers and tailored pieces'
                    ]
                },
                'Bohemian Spirit': {
                    description: 'You have a free-spirited approach to fashion with a love for artistic expression.',
                    recommendations: [
                        'Flowy dresses and skirts',
                        'Natural fabrics and textures',
                        'Layered jewelry and accessories'
                    ]
                },
                'Urban Casual': {
                    description: 'You value comfort without sacrificing style, perfect for a modern lifestyle.',
                    recommendations: [
                        'Premium basics with interesting details',
                        'Smart casual pieces',
                        'Versatile layering pieces'
                    ]
                }
            };

            // Determine personality based on answers
            const personality = this.determinePersonality(this.answers);
            return {
                personality,
                ...personalities[personality]
            };
        },

        determinePersonality(answers) {
            // Simple algorithm to determine style personality
            const stylePoints = {
                'Trendsetter': 0,
                'Classic Minimalist': 0,
                'Bohemian Spirit': 0,
                'Urban Casual': 0
            };

            // Analyze answers and increment points
            answers.forEach(answer => {
                if (['Bold & Bright', 'Trendy', 'Fitted', '2010s'].includes(answer)) {
                    stylePoints['Trendsetter']++;
                }
                if (['Neutrals', 'Professional', 'Structured', 'Timeless'].includes(answer)) {
                    stylePoints['Classic Minimalist']++;
                }
                if (['Pastels', 'Loose & Flowy', 'Mix of Both', '2000s'].includes(answer)) {
                    stylePoints['Bohemian Spirit']++;
                }
                if (['Earth Tones', 'Casual', 'Mix of Both', '90s'].includes(answer)) {
                    stylePoints['Urban Casual']++;
                }
            });

            // Return personality with highest points
            return Object.entries(stylePoints)
                .reduce((a, b) => a[1] > b[1] ? a : b)[0];
        }
    };

    // Outfit Calendar
    const outfitCalendar = {
        currentDate: new Date(),
        selectedDate: null,
        events: JSON.parse(localStorage.getItem('calendarEvents') || '[]'),

        init() {
            this.renderCalendar();
            this.attachEventListeners();
        },

        renderCalendar() {
            const grid = document.querySelector('.calendar-grid');
            const monthYear = document.querySelector('.current-month');
            const firstDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const lastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
            
            monthYear.textContent = this.currentDate.toLocaleDateString('default', { 
                month: 'long', 
                year: 'numeric' 
            });

            let days = [];
            
            // Add empty cells for days before first of month
            for (let i = 0; i < firstDay.getDay(); i++) {
                days.push('<div class="calendar-day empty"></div>');
            }

            // Add days of month
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const date = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), i);
                const event = this.getEvent(date);
                const isToday = this.isToday(date);
                
                days.push(`
                    <div class="calendar-day ${event ? 'has-outfit' : ''} ${isToday ? 'today' : ''}"
                         data-date="${date.toISOString()}">
                        <div class="day-number">${i}</div>
                        ${event ? `
                            <div class="day-outfit">
                                <small>${event.outfit}</small>
                            </div>
                        ` : ''}
                    </div>
                `);
            }

            grid.innerHTML = days.join('');
        },

        attachEventListeners() {
            document.querySelector('.prev-month').addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.renderCalendar();
            });

            document.querySelector('.next-month').addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.renderCalendar();
            });
        },

        getEvent(date) {
            return this.events.find(event => 
                new Date(event.date).toDateString() === date.toDateString()
            );
        },

        isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        },

        addEvent(date, outfit) {
            this.events.push({ date: date.toISOString(), outfit });
            localStorage.setItem('calendarEvents', JSON.stringify(this.events));
            this.renderCalendar();
        }
    };

    // Initialize new features
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize wardrobe filters
        document.getElementById('categoryFilter').addEventListener('change', () => wardrobe.renderItems());
        document.getElementById('seasonFilter').addEventListener('change', () => wardrobe.renderItems());

        // Initialize outfit calendar
        outfitCalendar.init();

        // Add navigation menu
        const navMenu = document.createElement('div');
        navMenu.className = 'nav-menu';
        navMenu.innerHTML = `
            <button class="nav-item active" data-section="main-form">Outfit Generator</button>
            <button class="nav-item" data-section="virtual-wardrobe">Virtual Wardrobe</button>
            <button class="nav-item" data-section="mix-match">Mix & Match</button>
            <button class="nav-item" data-section="style-quiz">Style Quiz</button>
            <button class="nav-item" data-section="outfit-calendar">Outfit Calendar</button>
        `;

        document.querySelector('.container').prepend(navMenu);

        // Handle navigation
        navMenu.addEventListener('click', (e) => {
            if (e.target.classList.contains('nav-item')) {
                // Update active state
                document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
                e.target.classList.add('active');

                // Show selected section
                const section = e.target.dataset.section;
                document.querySelectorAll('.virtual-wardrobe, .mix-match, .style-quiz, .outfit-calendar, .main-form')
                    .forEach(el => el.style.display = 'none');
                document.querySelector(`.${section}`).style.display = 'block';

                // Initialize specific features
                if (section === 'style-quiz') {
                    styleQuiz.start();
                } else if (section === 'virtual-wardrobe') {
                    wardrobe.renderItems();
                }
            }
        });

        // Handle Add Item Modal
        const addItemBtn = document.querySelector('.add-item-btn');
        const modal = document.getElementById('addItemModal');
        const closeBtn = document.querySelector('.close');

        addItemBtn.addEventListener('click', () => {
            modal.style.display = 'block';
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Handle Add Item Form
        document.getElementById('addItemForm').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('itemName').value,
                category: document.getElementById('itemCategory').value,
                color: document.getElementById('itemColor').value,
                seasons: Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                    .map(cb => cb.value)
            };

            const fileInput = document.getElementById('itemImage');
            if (fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    formData.image = e.target.result;
                    wardrobe.addItem(formData);
                    modal.style.display = 'none';
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                wardrobe.addItem(formData);
                modal.style.display = 'none';
            }

            e.target.reset();
        });
    });
});

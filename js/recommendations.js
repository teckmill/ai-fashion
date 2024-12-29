// Recommendations and E-commerce Integration

class RecommendationSystem {
    constructor() {
        this.recommendations = [];
        this.initializeEventListeners();
    }

    async initializeEventListeners() {
        // Track product views
        document.addEventListener('click', async (e) => {
            const productCard = e.target.closest('.product-card');
            if (productCard) {
                const productId = productCard.dataset.productId;
                const itemType = productCard.dataset.itemType;
                await this.trackInteraction('view', productId, itemType);
            }
        });

        // Track product likes
        document.querySelectorAll('.product-like-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const productCard = e.target.closest('.product-card');
                const productId = productCard.dataset.productId;
                const itemType = productCard.dataset.itemType;
                await this.trackInteraction('like', productId, itemType);
                btn.classList.toggle('liked');
            });
        });

        // Track product saves
        document.querySelectorAll('.product-save-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const productCard = e.target.closest('.product-card');
                const productId = productCard.dataset.productId;
                const itemType = productCard.dataset.itemType;
                await this.trackInteraction('save', productId, itemType);
                btn.classList.toggle('saved');
            });
        });
    }

    async getRecommendations() {
        try {
            const response = await fetch('/ai-fashion/api/recommendations/get.php');
            const data = await response.json();
            
            if (data.success) {
                this.recommendations = data.recommendations;
                this.displayRecommendations();
            } else {
                console.error('Failed to get recommendations:', data.message);
            }
        } catch (error) {
            console.error('Error getting recommendations:', error);
        }
    }

    async trackInteraction(interactionType, itemId, itemType, interactionData = null) {
        try {
            const response = await fetch('/ai-fashion/api/recommendations/track.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    interaction_type: interactionType,
                    item_id: itemId,
                    item_type: itemType,
                    interaction_data: interactionData
                })
            });
            
            const data = await response.json();
            if (!data.success) {
                console.error('Failed to track interaction:', data.message);
            }
        } catch (error) {
            console.error('Error tracking interaction:', error);
        }
    }

    displayRecommendations() {
        const container = document.querySelector('.recommendations-container');
        if (!container) return;

        container.innerHTML = this.recommendations.map(product => this.createProductCard(product)).join('');
    }

    createProductCard(product) {
        return `
            <div class="product-card" data-product-id="${product.product_id}" data-item-type="${product.category}">
                <div class="product-image">
                    <img src="${product.image_url}" alt="${product.product_name}">
                    <div class="product-actions">
                        <button class="product-like-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="product-save-btn">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">${product.product_name}</h3>
                    <p class="product-price">$${product.price}</p>
                    <a href="${product.product_url}" target="_blank" class="buy-now-btn">Buy Now</a>
                </div>
            </div>
        `;
    }

    async refreshRecommendations() {
        await this.getRecommendations();
    }
}

// Initialize the recommendation system
document.addEventListener('DOMContentLoaded', () => {
    const recommendationSystem = new RecommendationSystem();
    recommendationSystem.getRecommendations();

    // Refresh recommendations periodically
    setInterval(() => {
        recommendationSystem.refreshRecommendations();
    }, 300000); // Refresh every 5 minutes
});

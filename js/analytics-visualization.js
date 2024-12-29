// Import Chart.js
import Chart from 'chart.js/auto';

class AnalyticsVisualization {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: '#4A90E2',
            secondary: '#F5A623',
            tertiary: '#7ED321',
            quaternary: '#9013FE',
            background: 'rgba(255, 255, 255, 0.9)'
        };
    }

    async initialize() {
        await this.loadStyleEvolution();
        await this.loadWardrobeInsights();
        await this.loadSeasonalAnalysis();
        await this.loadColorAnalysis();
        this.setupEventListeners();
    }

    async loadStyleEvolution() {
        try {
            const response = await fetch('/api/analytics/style-evolution');
            const data = await response.json();
            
            const ctx = document.getElementById('styleEvolutionChart').getContext('2d');
            this.charts.styleEvolution = new Chart(ctx, {
                type: 'line',
                data: this.formatStyleEvolutionData(data),
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Style Evolution Over Time'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading style evolution:', error);
        }
    }

    async loadWardrobeInsights() {
        try {
            const response = await fetch('/api/analytics/wardrobe-insights');
            const data = await response.json();
            
            // Most Worn Items Chart
            const mostWornCtx = document.getElementById('mostWornChart').getContext('2d');
            this.charts.mostWorn = new Chart(mostWornCtx, {
                type: 'bar',
                data: this.formatWardrobeData(data.most_worn),
                options: {
                    indexAxis: 'y',
                    plugins: {
                        title: {
                            display: true,
                            text: 'Most Worn Items'
                        }
                    }
                }
            });

            // Style Distribution Chart
            const distributionCtx = document.getElementById('styleDistributionChart').getContext('2d');
            this.charts.styleDistribution = new Chart(distributionCtx, {
                type: 'doughnut',
                data: this.formatStyleDistributionData(data.style_distribution),
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Style Distribution'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading wardrobe insights:', error);
        }
    }

    async loadSeasonalAnalysis() {
        try {
            const response = await fetch('/api/analytics/seasonal-analysis');
            const data = await response.json();
            
            const ctx = document.getElementById('seasonalAnalysisChart').getContext('2d');
            this.charts.seasonalAnalysis = new Chart(ctx, {
                type: 'radar',
                data: this.formatSeasonalData(data),
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Seasonal Wardrobe Analysis'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading seasonal analysis:', error);
        }
    }

    async loadColorAnalysis() {
        try {
            const response = await fetch('/api/analytics/color-analysis');
            const data = await response.json();
            
            const ctx = document.getElementById('colorAnalysisChart').getContext('2d');
            this.charts.colorAnalysis = new Chart(ctx, {
                type: 'polarArea',
                data: this.formatColorData(data),
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Color Distribution'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error loading color analysis:', error);
        }
    }

    formatStyleEvolutionData(data) {
        const datasets = {};
        
        data.forEach(item => {
            if (!datasets[item.style_category]) {
                datasets[item.style_category] = {
                    label: item.style_category,
                    data: [],
                    borderColor: this.getRandomColor(),
                    fill: false
                };
            }
            datasets[item.style_category].data.push({
                x: item.period,
                y: item.usage_count
            });
        });

        return {
            datasets: Object.values(datasets)
        };
    }

    formatWardrobeData(data) {
        return {
            labels: data.map(item => item.name),
            datasets: [{
                label: 'Times Worn',
                data: data.map(item => item.wear_count),
                backgroundColor: this.colors.primary
            }]
        };
    }

    formatStyleDistributionData(data) {
        return {
            labels: data.map(item => item.style_category),
            datasets: [{
                data: data.map(item => item.percentage),
                backgroundColor: this.generateColorPalette(data.length)
            }]
        };
    }

    formatSeasonalData(data) {
        const seasons = ['Spring', 'Summer', 'Fall', 'Winter'];
        const categories = [...new Set(data.map(item => item.category))];
        
        return {
            labels: seasons,
            datasets: categories.map(category => ({
                label: category,
                data: seasons.map(season => {
                    const seasonData = data.find(item => 
                        item.season.toLowerCase() === season.toLowerCase() && 
                        item.category === category
                    );
                    return seasonData ? seasonData.usage_count : 0;
                }),
                backgroundColor: this.getRandomColor(0.2),
                borderColor: this.getRandomColor(1)
            }))
        };
    }

    formatColorData(data) {
        return {
            labels: data.map(item => item.primary_color),
            datasets: [{
                data: data.map(item => item.percentage),
                backgroundColor: data.map(item => item.primary_color)
            }]
        };
    }

    getRandomColor(alpha = 1) {
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    generateColorPalette(count) {
        return Array(count).fill(0).map(() => this.getRandomColor());
    }

    setupEventListeners() {
        // Time range selector
        document.getElementById('timeRange').addEventListener('change', (e) => {
            this.updateTimeRange(e.target.value);
        });

        // Chart type toggles
        document.querySelectorAll('.chart-type-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                this.toggleChartType(e.target.dataset.chartId);
            });
        });
    }

    async updateTimeRange(range) {
        try {
            const response = await fetch(`/api/analytics/style-evolution?timeframe=${range}`);
            const data = await response.json();
            
            this.charts.styleEvolution.data = this.formatStyleEvolutionData(data);
            this.charts.styleEvolution.update();
        } catch (error) {
            console.error('Error updating time range:', error);
        }
    }

    toggleChartType(chartId) {
        const chart = this.charts[chartId];
        if (!chart) return;

        const types = ['bar', 'line', 'radar', 'polarArea', 'doughnut'];
        const currentIndex = types.indexOf(chart.config.type);
        const nextType = types[(currentIndex + 1) % types.length];

        chart.config.type = nextType;
        chart.update();
    }
}

// Initialize analytics visualization
document.addEventListener('DOMContentLoaded', () => {
    const analytics = new AnalyticsVisualization();
    analytics.initialize();
});

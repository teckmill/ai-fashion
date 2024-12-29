<?php
require_once '../includes/functions.php';
require_once '../includes/localization.php';

if (!isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

$loc = Localization::getInstance();
?>

<!DOCTYPE html>
<html lang="<?php echo $loc->getCurrentLocale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $loc->translate('analytics.title'); ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/analytics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="analytics-container">
        <h1><?php echo $loc->translate('analytics.title'); ?></h1>

        <!-- Time Range Selector -->
        <div class="time-range-selector">
            <label for="timeRange"><?php echo $loc->translate('analytics.time_range'); ?></label>
            <select id="timeRange">
                <option value="month">Last Month</option>
                <option value="quarter">Last Quarter</option>
                <option value="year" selected>Last Year</option>
                <option value="all">All Time</option>
            </select>
        </div>

        <!-- Analytics Grid -->
        <div class="analytics-grid">
            <!-- Style Evolution -->
            <div class="analytics-card">
                <h2><?php echo $loc->translate('analytics.style_evolution'); ?></h2>
                <div class="chart-container">
                    <canvas id="styleEvolutionChart"></canvas>
                </div>
                <button class="chart-type-toggle" data-chart-id="styleEvolution">
                    Change Chart Type
                </button>
            </div>

            <!-- Most Worn Items -->
            <div class="analytics-card">
                <h2><?php echo $loc->translate('analytics.most_worn'); ?></h2>
                <div class="chart-container">
                    <canvas id="mostWornChart"></canvas>
                </div>
            </div>

            <!-- Style Distribution -->
            <div class="analytics-card">
                <h2><?php echo $loc->translate('analytics.style_distribution'); ?></h2>
                <div class="chart-container">
                    <canvas id="styleDistributionChart"></canvas>
                </div>
            </div>

            <!-- Seasonal Analysis -->
            <div class="analytics-card">
                <h2><?php echo $loc->translate('analytics.seasonal_analysis'); ?></h2>
                <div class="chart-container">
                    <canvas id="seasonalAnalysisChart"></canvas>
                </div>
            </div>

            <!-- Color Analysis -->
            <div class="analytics-card">
                <h2><?php echo $loc->translate('analytics.color_analysis'); ?></h2>
                <div class="chart-container">
                    <canvas id="colorAnalysisChart"></canvas>
                </div>
            </div>

            <!-- Style Insights -->
            <div class="analytics-card insights">
                <h2><?php echo $loc->translate('analytics.insights'); ?></h2>
                <div id="styleInsights" class="insights-container">
                    <!-- Insights will be loaded dynamically -->
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script type="module" src="/js/analytics-visualization.js"></script>
    <script>
        // Initialize tooltips and other UI elements
        document.addEventListener('DOMContentLoaded', () => {
            // Load initial insights
            fetch('/api/analytics/insights')
                .then(response => response.json())
                .then(data => {
                    const insightsContainer = document.getElementById('styleInsights');
                    data.insights.forEach(insight => {
                        const insightElement = document.createElement('div');
                        insightElement.className = 'insight-item';
                        insightElement.innerHTML = `
                            <h3>${insight.title}</h3>
                            <p>${insight.description}</p>
                        `;
                        insightsContainer.appendChild(insightElement);
                    });
                })
                .catch(error => console.error('Error loading insights:', error));
        });
    </script>
</body>
</html>

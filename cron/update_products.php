<?php
require_once '../includes/scraper.php';

// Set longer execution time for scraping
set_time_limit(3600); // 1 hour
ini_set('memory_limit', '512M');

// Initialize scraper
$scraper = new ProductScraper();

// Update database with new products
$scraper->updateDatabase();

// Log the update
$logMessage = date('Y-m-d H:i:s') . " - Products updated\n";
file_put_contents('../logs/scraper.log', $logMessage, FILE_APPEND);

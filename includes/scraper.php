<?php
require_once 'config.php';
require_once 'simple_html_dom.php'; // We'll need to download this library

class ProductScraper {
    private $sources = [
        'hm' => [
            'base_url' => 'https://www2.hm.com',
            'categories' => [
                'tops' => '/en_us/women/tops',
                'dresses' => '/en_us/women/dresses',
                'bottoms' => '/en_us/women/pants',
                'shoes' => '/en_us/women/shoes',
                'accessories' => '/en_us/women/accessories'
            ]
        ],
        'zara' => [
            'base_url' => 'https://www.zara.com',
            'categories' => [
                'tops' => '/us/woman/shirts-tops',
                'dresses' => '/us/woman/dresses',
                'bottoms' => '/us/woman/pants',
                'shoes' => '/us/woman/shoes',
                'accessories' => '/us/woman/accessories'
            ]
        ]
    ];

    public function scrapeProducts($source = 'all', $category = null) {
        if (!file_exists('simple_html_dom.php')) {
            $this->downloadSimpleHtmlDom();
        }

        $products = [];
        
        if ($source === 'all') {
            foreach ($this->sources as $sourceName => $sourceData) {
                $products = array_merge($products, $this->scrapeSource($sourceName, $category));
            }
        } else if (isset($this->sources[$source])) {
            $products = $this->scrapeSource($source, $category);
        }

        return $products;
    }

    private function scrapeSource($source, $category = null) {
        $sourceData = $this->sources[$source];
        $products = [];

        $categories = $category ? [$category => $sourceData['categories'][$category]] : $sourceData['categories'];

        foreach ($categories as $catName => $catUrl) {
            $url = $sourceData['base_url'] . $catUrl;
            $products = array_merge($products, $this->scrapePage($url, $source, $catName));
        }

        return $products;
    }

    private function scrapePage($url, $source, $category) {
        try {
            // Use cURL to get the page content
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $html = curl_exec($ch);
            curl_close($ch);

            if (!$html) {
                throw new Exception("Failed to fetch URL: $url");
            }

            // Create a DOM object
            $dom = str_get_html($html);
            $products = [];

            // Different parsing logic for different sources
            switch ($source) {
                case 'hm':
                    $products = $this->parseHMProducts($dom, $category);
                    break;
                case 'zara':
                    $products = $this->parseZaraProducts($dom, $category);
                    break;
            }

            return $products;
        } catch (Exception $e) {
            error_log("Scraping error for $url: " . $e->getMessage());
            return [];
        }
    }

    private function parseHMProducts($dom, $category) {
        $products = [];
        
        // Find product elements
        foreach ($dom->find('div.product-item') as $item) {
            try {
                $name = $item->find('h3.product-item-heading', 0)->plaintext;
                $price = $this->extractPrice($item->find('span.price', 0)->plaintext);
                $imageUrl = $item->find('img.product-item-image', 0)->src;
                $productUrl = $item->find('a.product-item-link', 0)->href;
                
                $products[] = [
                    'name' => trim($name),
                    'price' => $price,
                    'image_url' => $imageUrl,
                    'product_url' => 'https://www2.hm.com' . $productUrl,
                    'category' => $category,
                    'brand' => 'H&M'
                ];
            } catch (Exception $e) {
                continue; // Skip if any required element is missing
            }
        }
        
        return $products;
    }

    private function parseZaraProducts($dom, $category) {
        $products = [];
        
        // Find product elements
        foreach ($dom->find('div.product-grid-item') as $item) {
            try {
                $name = $item->find('a.name', 0)->plaintext;
                $price = $this->extractPrice($item->find('span.price', 0)->plaintext);
                $imageUrl = $item->find('img.product-image', 0)->src;
                $productUrl = $item->find('a.product-link', 0)->href;
                
                $products[] = [
                    'name' => trim($name),
                    'price' => $price,
                    'image_url' => $imageUrl,
                    'product_url' => 'https://www.zara.com' . $productUrl,
                    'category' => $category,
                    'brand' => 'Zara'
                ];
            } catch (Exception $e) {
                continue; // Skip if any required element is missing
            }
        }
        
        return $products;
    }

    private function extractPrice($priceString) {
        // Remove currency symbols and convert to float
        return (float) preg_replace('/[^0-9.]/', '', $priceString);
    }

    private function saveProducts($products) {
        global $pdo;
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, price, category, image_url, affiliate_url, brand)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            price = VALUES(price),
            image_url = VALUES(image_url)
        ");

        foreach ($products as $product) {
            try {
                $stmt->execute([
                    $product['name'],
                    $product['price'],
                    $product['category'],
                    $product['image_url'],
                    $product['product_url'],
                    $product['brand']
                ]);
            } catch (PDOException $e) {
                error_log("Error saving product: " . $e->getMessage());
                continue;
            }
        }
    }

    private function downloadSimpleHtmlDom() {
        $url = 'https://sourceforge.net/projects/simplehtmldom/files/latest/download';
        $content = file_get_contents($url);
        file_put_contents('simple_html_dom.php', $content);
    }

    public function updateDatabase() {
        $products = $this->scrapeProducts();
        $this->saveProducts($products);
    }
}

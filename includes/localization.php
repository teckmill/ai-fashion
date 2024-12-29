<?php
class Localization {
    private static $instance = null;
    private $translations = [];
    private $currentLocale = 'en';
    private $supportedLocales = [
        'en' => ['name' => 'English', 'currency' => 'USD', 'currency_symbol' => '$', 'measurement' => 'imperial'],
        'es' => ['name' => 'Español', 'currency' => 'EUR', 'currency_symbol' => '€', 'measurement' => 'metric'],
        'fr' => ['name' => 'Français', 'currency' => 'EUR', 'currency_symbol' => '€', 'measurement' => 'metric'],
        'de' => ['name' => 'Deutsch', 'currency' => 'EUR', 'currency_symbol' => '€', 'measurement' => 'metric'],
        'it' => ['name' => 'Italiano', 'currency' => 'EUR', 'currency_symbol' => '€', 'measurement' => 'metric'],
        'zh' => ['name' => '中文', 'currency' => 'CNY', 'currency_symbol' => '¥', 'measurement' => 'metric'],
        'ja' => ['name' => '日本語', 'currency' => 'JPY', 'currency_symbol' => '¥', 'measurement' => 'metric']
    ];

    private function __construct() {
        $this->loadTranslations();
        $this->setLocaleFromUser();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadTranslations() {
        foreach ($this->supportedLocales as $locale => $data) {
            $file = __DIR__ . "/../lang/{$locale}.json";
            if (file_exists($file)) {
                $this->translations[$locale] = json_decode(file_get_contents($file), true);
            }
        }
    }

    private function setLocaleFromUser() {
        if (isset($_SESSION['user_locale'])) {
            $this->currentLocale = $_SESSION['user_locale'];
        } else {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
            $this->currentLocale = isset($this->supportedLocales[$browserLang]) ? $browserLang : 'en';
        }
    }

    public function setLocale($locale) {
        if (isset($this->supportedLocales[$locale])) {
            $this->currentLocale = $locale;
            $_SESSION['user_locale'] = $locale;
            return true;
        }
        return false;
    }

    public function translate($key, $params = []) {
        $translation = $this->translations[$this->currentLocale][$key] ?? $this->translations['en'][$key] ?? $key;
        
        foreach ($params as $param => $value) {
            $translation = str_replace(":{$param}", $value, $translation);
        }
        
        return $translation;
    }

    public function formatCurrency($amount, $locale = null) {
        $locale = $locale ?? $this->currentLocale;
        $localeData = $this->supportedLocales[$locale];
        
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $localeData['currency']);
    }

    public function convertMeasurement($value, $from = 'imperial', $to = null) {
        $to = $to ?? $this->supportedLocales[$this->currentLocale]['measurement'];
        
        if ($from === $to) return $value;
        
        if ($from === 'imperial' && $to === 'metric') {
            return [
                'height' => $value * 2.54, // inches to cm
                'weight' => $value * 0.453592 // lbs to kg
            ];
        } else {
            return [
                'height' => $value / 2.54, // cm to inches
                'weight' => $value / 0.453592 // kg to lbs
            ];
        }
    }

    public function getCurrentLocale() {
        return $this->currentLocale;
    }

    public function getSupportedLocales() {
        return $this->supportedLocales;
    }
}

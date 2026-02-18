<?php
/**
 * PonponPay Language Helper
 *
 * Handles multi-language support for the PonponPay payment gateway.
 *
 * @package    PonponPay
 * @author     PonponPay Engineering
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * PonponPay Language Class
 */
class PonponPayLanguage
{
    /**
     * @var array Language strings
     */
    private static $lang = [];

    /**
     * @var string Current language
     */
    private static $currentLang = 'english';

    /**
     * @var bool Whether language has been loaded
     */
    private static $loaded = false;

    /**
     * Language mapping from WHMCS language names to PonponPay language files
     */
    private static $languageMap = [
        'english' => 'english',
        'chinese' => 'chinese',
        'japanese' => 'japanese',
        'korean' => 'korean',
        'spanish' => 'spanish',
        'french' => 'french',
        'german' => 'german',
        'portuguese-br' => 'portuguese',
        'portuguese-pt' => 'portuguese',
        'russian' => 'russian',
        'arabic' => 'arabic',
        // Add more mappings as needed
    ];

    /**
     * Initialize and load language file
     *
     * @return void
     */
    public static function init()
    {
        if (self::$loaded) {
            return;
        }

        // Detect WHMCS language
        self::$currentLang = self::detectLanguage();

        // Load language file
        self::loadLanguageFile();

        self::$loaded = true;
    }

    /**
     * Detect current WHMCS language
     * Priority: User account language > Session language > Cookie > System default
     *
     * @return string Language name
     */
    private static function detectLanguage()
    {
        $whmcsLang = 'english';

        // 1. First priority: Get language from logged-in user's account settings
        if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
            try {
                // Use WHMCS localAPI to get client details
                if (function_exists('localAPI')) {
                    $clientDetails = localAPI('GetClientsDetails', [
                        'clientid' => $_SESSION['uid'],
                        'stats' => false
                    ]);
                    
                    if ($clientDetails['result'] === 'success' && !empty($clientDetails['language'])) {
                        $whmcsLang = strtolower($clientDetails['language']);
                        if (isset(self::$languageMap[$whmcsLang])) {
                            return self::$languageMap[$whmcsLang];
                        }
                    }
                }
            } catch (Exception $e) {
                // Silently fail and continue to fallback methods
                error_log("[PonponPay] Failed to get user language: " . $e->getMessage());
            }
        }

        // 2. Second priority: Check current session language (user's browser selection)
        if (isset($_SESSION['Language']) && !empty($_SESSION['Language'])) {
            $whmcsLang = strtolower($_SESSION['Language']);
            if (isset(self::$languageMap[$whmcsLang])) {
                return self::$languageMap[$whmcsLang];
            }
        }

        // 3. Third priority: Check cookie
        if (isset($_COOKIE['WHMCSLanguage']) && !empty($_COOKIE['WHMCSLanguage'])) {
            $whmcsLang = strtolower($_COOKIE['WHMCSLanguage']);
            if (isset(self::$languageMap[$whmcsLang])) {
                return self::$languageMap[$whmcsLang];
            }
        }

        // 4. Fourth priority: Check global WHMCS config (system default)
        if (isset($GLOBALS['CONFIG']['Language']) && !empty($GLOBALS['CONFIG']['Language'])) {
            $whmcsLang = strtolower($GLOBALS['CONFIG']['Language']);
            if (isset(self::$languageMap[$whmcsLang])) {
                return self::$languageMap[$whmcsLang];
            }
        }

        // Default to English
        return 'english';
    }

    /**
     * Load language file
     *
     * @return void
     */
    private static function loadLanguageFile()
    {
        $langDir = dirname(__DIR__) . '/lang/';
        $langFile = $langDir . self::$currentLang . '.php';

        // Fallback to English if language file doesn't exist
        if (!file_exists($langFile)) {
            $langFile = $langDir . 'english.php';
            self::$currentLang = 'english';
        }

        if (file_exists($langFile)) {
            include $langFile;
            if (isset($_PONPONPAY_LANG) && is_array($_PONPONPAY_LANG)) {
                self::$lang = $_PONPONPAY_LANG;
            }
        }

        // Ensure we have at least empty array
        if (empty(self::$lang)) {
            self::$lang = [];
        }
    }

    /**
     * Get translated string
     *
     * @param string $key Language key
     * @param mixed ...$args Optional sprintf arguments
     * @return string Translated string or key if not found
     */
    public static function get($key, ...$args)
    {
        self::init();

        if (!isset(self::$lang[$key])) {
            // Return key as fallback
            return $key;
        }

        $text = self::$lang[$key];

        // Apply sprintf if arguments provided
        if (!empty($args)) {
            $text = sprintf($text, ...$args);
        }

        return $text;
    }

    /**
     * Get all language strings
     *
     * @return array All language strings
     */
    public static function getAll()
    {
        self::init();
        return self::$lang;
    }

    /**
     * Get current language name
     *
     * @return string Current language
     */
    public static function getCurrentLanguage()
    {
        self::init();
        return self::$currentLang;
    }

    /**
     * Check if a language key exists
     *
     * @param string $key Language key
     * @return bool
     */
    public static function has($key)
    {
        self::init();
        return isset(self::$lang[$key]);
    }

    /**
     * Force reload language (useful for testing)
     *
     * @param string|null $lang Language to load
     * @return void
     */
    public static function reload($lang = null)
    {
        self::$loaded = false;
        self::$lang = [];

        if ($lang !== null) {
            self::$currentLang = $lang;
        }

        self::init();
    }
}

/**
 * Shorthand function for getting translated strings
 *
 * @param string $key Language key
 * @param mixed ...$args Optional sprintf arguments
 * @return string Translated string
 */
function ponponpay_lang($key, ...$args)
{
    return PonponPayLanguage::get($key, ...$args);
}

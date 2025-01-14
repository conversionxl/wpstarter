<?php
/**
 * This file is generated by WP Starter package, and contains base configuration of the WordPress.
 *
 * All the configuration constants used by WordPress are set via environment variables.
 * Default settings are provided in this file for most common settings, however database settings
 * are required, you can get them from your web host.
 *
 */
use WeCodeMore\WpStarter\Env\WordPressEnvBridge;

DEBUG_INFO_INIT: {
    $debugInfo = [];
} #@@/DEBUG_INFO_INIT

ABSPATH: {
    /** Absolute path to the WordPress directory. */
    defined('ABSPATH') or define('ABSPATH', realpath(__DIR__ . '{{{WP_INSTALL_PATH}}}') . '/');

    /**
     * Load plugin.php early, so we can call hooks from here on.
     * E.g. in Composer-autoloaded "files".
     */
    require_once ABSPATH . 'wp-includes/plugin.php';
} #@@/ABSPATH

AUTOLOAD: {
    /** Composer autoload. */
    require_once realpath(__DIR__ . '{{{AUTOLOAD_PATH}}}');

    define('WPSTARTER_PATH', realpath(__DIR__ . '{{{ENV_REL_PATH}}}'));

    $debugInfo['autoload-path'] = [
        'label' => 'Autoload path',
        'value' => __DIR__ . '{{{AUTOLOAD_PATH}}}',
        'debug' => __DIR__ . '{{{AUTOLOAD_PATH}}}'
    ];
    $debugInfo['base-path'] = [
        'label' => 'Base path',
        'value' => WPSTARTER_PATH,
        'debug' => WPSTARTER_PATH,
    ];
} #@@/AUTOLOAD

ENV_VARIABLES: {
    /**
     * Define all WordPress constants from environment variables.
     * Environment variables will be loaded from file, unless `WPSTARTER_ENV_LOADED` env var is already
     * setup e.g. via webserver configuration.
     * In that case all environment variables are assumed to be set.
     * Environment variables that are set in the *real* environment (e.g. via webserver) will not be
     * overridden from file, even if `WPSTARTER_ENV_LOADED` is not set.
     */
    $envCacheEnabled = filter_var('{{{CACHE_ENV}}}', FILTER_VALIDATE_BOOLEAN);
    $envLoader = $envCacheEnabled
        ? WordPressEnvBridge::buildFromCacheDump(WPSTARTER_PATH . WordPressEnvBridge::CACHE_DUMP_FILE)
        : new WordPressEnvBridge();

    $envIsCached = $envLoader->hasCachedValues();
    if (!$envIsCached) {
        $envLoader->load('{{{ENV_FILE_NAME}}}', WPSTARTER_PATH);
        $envType = $envLoader->determineEnvType();
        if ($envType !== 'example') {
            $envLoader->loadAppended("{{{ENV_FILE_NAME}}}.{$envType}", WPSTARTER_PATH);
        }
    }
    /**
     * Core wp_get_environment_type() only supports a pre-defined list of environments types.
     * WP Starter tries to map different environments to values supported by core, for example
     * "dev" (or "develop", or even "develop-1") will be mapped to "development" accepted by WP.
     * In that case, `wp_get_environment_type()` will return "development", but `WP_ENV` will still
     * be "dev" (or "develop", or "develop-1").
     */
    $envIsCached ? $envLoader->setupEnvConstants() : $envLoader->setupConstants();
    isset($envType) or $envType = $envLoader->determineEnvType();

    $debugInfo['env-cache-file'] = [
        'label' => 'Env cache file',
        'value' => WPSTARTER_PATH . WordPressEnvBridge::CACHE_DUMP_FILE,
        'debug' => WPSTARTER_PATH . WordPressEnvBridge::CACHE_DUMP_FILE,
    ];
    $debugInfo['env-cache-enabled'] = [
        'label' => 'Env cache enabled',
        'value' => $envCacheEnabled ? 'Yes' : 'No',
        'debug' => $envCacheEnabled,
    ];
    $debugInfo['cached-env'] = [
        'label' => 'Is env loaded from cache',
        'value' => $envIsCached ? 'Yes' : 'No',
        'debug' => $envIsCached,
    ];
    $debugInfo['env-type'] = [
        'label' => 'Env type',
        'value' => $envType,
        'debug' => $envType,
    ];

    unset($envCacheEnabled, $envIsCached);

    $phpEnvFilePath = WPSTARTER_PATH . "/{$envType}.php";
    $hasPhpEnvFile = file_exists($phpEnvFilePath) && is_readable($phpEnvFilePath);
    if ($hasPhpEnvFile) {
        require_once WPSTARTER_PATH . "/{$envType}.php";
    }
    $debugInfo['env-php-file'] = [
        'label' => 'Env-specific PHP file',
        'value' => $hasPhpEnvFile ? WPSTARTER_PATH . "/{$envType}.php" : 'None',
        'debug' => $hasPhpEnvFile ? WPSTARTER_PATH . "/{$envType}.php" : '',
    ];
    unset($phpEnvFilePath, $hasPhpEnvFile);
} #@@/ENV_VARIABLES

KEYS: {
    /**#@+
     * Authentication Unique Keys and Salts.
     */
    defined('AUTH_KEY') or define('AUTH_KEY', '{{{AUTH_KEY}}}');
    defined('SECURE_AUTH_KEY') or define('SECURE_AUTH_KEY', '{{{SECURE_AUTH_KEY}}}');
    defined('LOGGED_IN_KEY') or define('LOGGED_IN_KEY', '{{{LOGGED_IN_KEY}}}');
    defined('NONCE_KEY') or define('NONCE_KEY', '{{{NONCE_KEY}}}');
    defined('AUTH_SALT') or define('AUTH_SALT', '{{{AUTH_SALT}}}');
    defined('SECURE_AUTH_SALT') or define('SECURE_AUTH_SALT', '{{{SECURE_AUTH_SALT}}}');
    defined('LOGGED_IN_SALT') or define('LOGGED_IN_SALT', '{{{LOGGED_IN_SALT}}}');
    defined('NONCE_SALT') or define('NONCE_SALT', '{{{NONCE_SALT}}}');
    /**#@-*/
} #@@/KEYS

DB_SETUP : {
    /** Set optional database settings if not already set. */
    defined('DB_HOST') or define('DB_HOST', 'localhost');
    defined('DB_CHARSET') or define('DB_CHARSET', 'utf8');
    defined('DB_COLLATE') or define('DB_COLLATE', '');

    /**
     * WordPress Database Table prefix.
     */
    global $table_prefix;
    $table_prefix = $envLoader->read('DB_TABLE_PREFIX') ?: 'wp_';
} #@@/DB_SETUP

EARLY_HOOKS : {
    /**
     * Load early hooks file if any.
     * Early hooks file allows adding hooks that are triggered before plugins are loaded, e.g.
     * "enable_loading_advanced_cache_dropin" or to just-in-time define configuration constants.
     */
    $earlyHookFile = '{{{EARLY_HOOKS_FILE}}}'
        && file_exists(__DIR__ . '{{{EARLY_HOOKS_FILE}}}')
        && is_readable(__DIR__ . '{{{EARLY_HOOKS_FILE}}}');
    if ($earlyHookFile) {
        require_once __DIR__ . '{{{EARLY_HOOKS_FILE}}}';
    }
    $debugInfo['early-hooks-file'] = [
        'label' => 'Early hooks file',
        'value' => $earlyHookFile ? __DIR__ . '{{{EARLY_HOOKS_FILE}}}' : 'None',
        'debug' => $earlyHookFile ? __DIR__ . '{{{EARLY_HOOKS_FILE}}}' : '',
    ];
    unset($earlyHookFile);
} #@@/EARLY_HOOKS

DEFAULT_ENV : {
    /** Environment-aware settings. Be creative, but avoid having sensitive settings here. */
    defined('WP_ENVIRONMENT_TYPE') or define('WP_ENVIRONMENT_TYPE', 'production');
    switch (WP_ENVIRONMENT_TYPE) {
        case 'local':
            defined('WP_LOCAL_DEV') or define('WP_LOCAL_DEV', true);
        case 'development':
            defined('WP_DEBUG') or define('WP_DEBUG', true);
            defined('WP_DEBUG_DISPLAY') or define('WP_DEBUG_DISPLAY', true);
            defined('WP_DEBUG_LOG') or define('WP_DEBUG_LOG', false);
            defined('SAVEQUERIES') or define('SAVEQUERIES', true);
            defined('SCRIPT_DEBUG') or define('SCRIPT_DEBUG', true);
            defined('WP_DISABLE_FATAL_ERROR_HANDLER') or define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
            break;
        case 'staging':
            defined('WP_DEBUG') or define('WP_DEBUG', true);
            defined('WP_DEBUG_DISPLAY') or define('WP_DEBUG_DISPLAY', false);
            defined('WP_DEBUG_LOG') or define('WP_DEBUG_LOG', true);
            defined('SAVEQUERIES') or define('SAVEQUERIES', false);
            defined('SCRIPT_DEBUG') or define('SCRIPT_DEBUG', true);
            break;
        case 'production':
        default:
            defined('WP_DEBUG') or define('WP_DEBUG', false);
            defined('WP_DEBUG_DISPLAY') or define('WP_DEBUG_DISPLAY', false);
            defined('WP_DEBUG_LOG') or define('WP_DEBUG_LOG', false);
            defined('SAVEQUERIES') or define('SAVEQUERIES', false);
            defined('SCRIPT_DEBUG') or define('SCRIPT_DEBUG', false);
            break;
    }
    $debugInfo['wp-env-type'] = [
        'label' => 'WordPress env type (used for defaults)',
        'value' => WP_ENVIRONMENT_TYPE,
        'debug' => WP_ENVIRONMENT_TYPE,
    ];
} #@@/DEFAULT_ENV

SSL_FIX : {
    $doSslFix = $envLoader->read('WP_FORCE_SSL_FORWARDED_PROTO')
        && array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER)
        && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
    $doSslFix and $_SERVER['HTTPS'] = 'on';
    $debugInfo['ssl_fix'] = [
        'label' => 'SSL fix for load balancers',
        'value' => $doSslFix ? 'Yes' : 'No',
        'debug' => $doSslFix,
    ];
    unset($doSslFix);
} #@@/SSL_FIX

URL_CONSTANTS : {
    if (!defined('WP_HOME')) {
        $home = filter_var($_SERVER['HTTPS'] ?? '', FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://';
        $home .= $_SERVER['SERVER_NAME'] ?? 'localhost';
        $port =  $_SERVER['SERVER_PORT'] ?? '';
        (is_numeric($port) && (int)$port > 0) and $home .= sprintf(':%d', $port);
        define('WP_HOME', $home);
        unset($home);
    }

    /** Set WordPress other URL / path constants not set via environment variables. */
    defined('WP_SITEURL') or define('WP_SITEURL', rtrim(WP_HOME, '/') . '/{{{WP_SITEURL_RELATIVE}}}');
    defined('WP_CONTENT_DIR') or define('WP_CONTENT_DIR', realpath(__DIR__ . '{{{WP_CONTENT_PATH}}}'));
    defined('WP_CONTENT_URL') or define('WP_CONTENT_URL', rtrim(WP_HOME, '/') . '/{{{WP_CONTENT_URL_RELATIVE}}}');
} #@@/URL_CONSTANTS

THEMES_REGISTER : {
    /** Register default themes inside WordPress package wp-content folder. */
    $registerThemeFolder = filter_var('{{{REGISTER_THEME_DIR}}}', FILTER_VALIDATE_BOOLEAN);
    $registerThemeFolder and add_action('plugins_loaded', static function () {
        register_theme_directory(ABSPATH . 'wp-content/themes');
    });
    $debugInfo['register-core-themes'] = [
        'label' => 'Register core themes folder',
        'value' => $registerThemeFolder,
        'debug' => '{{{REGISTER_THEME_DIR}}}',
    ];
    unset($registerThemeFolder);
} #@@/THEMES_REGISTER

ADMIN_COLOR : {
    /** Allow changing admin color scheme. Useful to distinguish environments in the dashboard. */
    add_filter(
        'get_user_option_admin_color',
        static function ($color) use ($envLoader) {
            return $envLoader->read('WP_ADMIN_COLOR') ?: $color;
        },
        999
    );
} #@@/ADMIN_COLOR

ENV_CACHE : {
    /** On shutdown, we dump environment so that on subsequent requests we can load it faster */
    if ('{{{CACHE_ENV}}}' && $envLoader->isWpSetup()) {
        register_shutdown_function(
            static function () use ($envLoader, $envType) {
                $isLocal = $envType === 'local';
                if (!apply_filters('wpstarter.skip-cache-env', $isLocal, $envType)) {
                    $envLoader->dumpCached(WPSTARTER_PATH . WordPressEnvBridge::CACHE_DUMP_FILE);
                }
            }
        );
    }
} #@@/ENV_CACHE

DEBUG_INFO : {
    add_filter(
        'debug_information',
        static function ($info) use ($debugInfo): array {
            is_array($info) or $info = [];
            $info['wp-starter'] = ['label' => 'WP Starter', 'fields' => $debugInfo];

            return $info;
        },
        30
    );
} #@@/DEBUG_INFO

GETENV_FILTER : {
    /**
     * A filter that can be used in place of `getenv` to get environment variables with benefits of
     * cache and filtering. Example: <code>$some_var = apply_filters('getenv', 'SOME_VAR');</code>
     */
    add_filter(
        'getenv',
        static function ($name) use ($envLoader) {
            return ($name && is_string($name)) ? $envLoader->read($name) : null;
        },
        PHP_INT_MAX
    );
} #@@/GETENV_FILTER

BEFORE_BOOTSTRAP : {
    /** A pre-defined section to extend configuration. */
} #@@/BEFORE_BOOTSTRAP

CLEAN_UP : {
    unset($debugInfo, $envType, $envLoader);
} #@@/CLEAN_UP

###################################################################################################
#  I've seen things you people wouldn't believe. Attack ships on fire off the shoulder of Orion.  #
#                 I watched C-beams glitter in the dark near the Tannhäuser Gate.                 #
#            All those moments will be lost in time, like tears in rain. Time to die.             #
###################################################################################################

/* That's all, stop editing! Happy blogging. */

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

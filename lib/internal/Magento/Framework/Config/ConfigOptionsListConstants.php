<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Config;

/**
 * Deployment configuration options constant storage
 * @api
 * @since 100.0.2
 */
class ConfigOptionsListConstants
{
    /**#@+
     * Path to the values in the deployment config
     */
    public const CONFIG_PATH_INSTALL_DATE = 'install/date';
    public const CONFIG_PATH_CRYPT_KEY = 'crypt/key';
    public const CONFIG_PATH_SESSION_SAVE = 'session/save';
    public const CONFIG_PATH_RESOURCE_DEFAULT_SETUP = 'resource/default_setup/connection';
    public const CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS = 'db/connection/default/driver_options';
    public const CONFIG_PATH_DB_CONNECTION_DEFAULT = 'db/connection/default';
    public const CONFIG_PATH_DB_CONNECTIONS = 'db/connection';
    public const CONFIG_PATH_DB_PREFIX = 'db/table_prefix';
    public const CONFIG_PATH_X_FRAME_OPT = 'x-frame-options';
    public const CONFIG_PATH_CACHE_HOSTS = 'http_cache_hosts';
    public const CONFIG_PATH_BACKEND = 'backend';
    public const CONFIG_PATH_INSTALL = 'install';
    public const CONFIG_PATH_CRYPT = 'crypt';
    public const CONFIG_PATH_SESSION = 'session';
    public const CONFIG_PATH_DB = 'db';
    public const CONFIG_PATH_RESOURCE = 'resource';
    public const CONFIG_PATH_CACHE_TYPES = 'cache_types';
    public const CONFIG_PATH_DOCUMENT_ROOT_IS_PUB = 'directories/document_root_is_pub';
    public const CONFIG_PATH_DB_LOGGER_OUTPUT = 'db_logger/output';
    public const CONFIG_PATH_DB_LOGGER_LOG_EVERYTHING = 'db_logger/log_everything';
    public const CONFIG_PATH_DB_LOGGER_QUERY_TIME_THRESHOLD = 'db_logger/query_time_threshold';
    public const CONFIG_PATH_DB_LOGGER_INCLUDE_STACKTRACE = 'db_logger/include_stacktrace';
    /**#@-*/

    /**
     * Parameter for disabling/enabling static content deployment on demand in production mode
     * Can contains 0/1 value
     */
    public const CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION = 'static_content_on_demand_in_production';

    /**
     * Parameter for forcing HTML minification even if file is already minified.
     */
    public const CONFIG_PATH_FORCE_HTML_MINIFICATION = 'force_html_minification';

    /**
     * Default limiting input array size for synchronous Web API
     */
    public const CONFIG_PATH_WEBAPI_SYNC_DEFAULT_INPUT_ARRAY_SIZE_LIMIT = 'webapi/sync/default_input_array_size_limit';

    /**
     * Default limiting input array size for asynchronous Web API
     * phpcs:disable
     */
    public const CONFIG_PATH_WEBAPI_ASYNC_DEFAULT_INPUT_ARRAY_SIZE_LIMIT = 'webapi/async/default_input_array_size_limit';
    //phpcs:enable

    /**#@+
     * Input keys for the options
     */
    public const INPUT_KEY_ENCRYPTION_KEY = 'key';
    public const INPUT_KEY_SESSION_SAVE = 'session-save';
    public const INPUT_KEY_DB_HOST = 'db-host';
    public const INPUT_KEY_DB_NAME = 'db-name';
    public const INPUT_KEY_DB_USER = 'db-user';
    public const INPUT_KEY_DB_PASSWORD = 'db-password';
    public const INPUT_KEY_DB_PREFIX = 'db-prefix';
    public const INPUT_KEY_DB_MODEL = 'db-model';
    public const INPUT_KEY_DB_INIT_STATEMENTS = 'db-init-statements';
    public const INPUT_KEY_DB_ENGINE = 'db-engine';
    public const INPUT_KEY_DB_SSL_KEY = 'db-ssl-key';
    public const INPUT_KEY_DB_SSL_CERT = 'db-ssl-cert';
    public const INPUT_KEY_DB_SSL_CA = 'db-ssl-ca';
    public const INPUT_KEY_DB_SSL_VERIFY = 'db-ssl-verify';
    public const INPUT_KEY_RESOURCE = 'resource';
    public const INPUT_KEY_SKIP_DB_VALIDATION = 'skip-db-validation';
    public const INPUT_KEY_CACHE_HOSTS = 'http-cache-hosts';
    /**#@-*/

    /**#@+
     * Input keys for cache configuration
     */
    public const KEY_CACHE_FRONTEND = 'cache/frontend';
    public const CONFIG_PATH_BACKEND_OPTIONS = 'backend_options';

    /**
     * Definition format constant.
     */
    public const INPUT_KEY_DEFINITION_FORMAT = 'definition-format';

    /**#@+
     * Values for session-save
     */
    public const SESSION_SAVE_FILES = 'files';
    public const SESSION_SAVE_DB = 'db';
    public const SESSION_SAVE_REDIS = 'redis';
    /**#@-*/

    /**
     * Array Key for session save method
     */
    public const KEY_SAVE = 'save';

    /**#@+
     * Array keys for Database configuration
     */
    public const KEY_HOST = 'host';
    public const KEY_PORT = 'port';
    public const KEY_NAME = 'dbname';
    public const KEY_USER = 'username';
    public const KEY_PASSWORD = 'password';
    public const KEY_ENGINE = 'engine';
    public const KEY_PREFIX = 'table_prefix';
    public const KEY_MODEL = 'model';
    public const KEY_INIT_STATEMENTS = 'initStatements';
    public const KEY_ACTIVE = 'active';
    public const KEY_DRIVER_OPTIONS = 'driver_options';
    /**#@-*/

    /**#@+
     * Array keys for database driver options configurations
     */
    public const KEY_MYSQL_SSL_KEY = \PDO::MYSQL_ATTR_SSL_KEY;
    public const KEY_MYSQL_SSL_CERT = \PDO::MYSQL_ATTR_SSL_CERT;
    public const KEY_MYSQL_SSL_CA = \PDO::MYSQL_ATTR_SSL_CA;

    /**
     * Constant \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT cannot be used as it was introduced in PHP 7.1.4
     * and Magento 2 is currently supporting PHP 7.1.3.
     */
    public const KEY_MYSQL_SSL_VERIFY = 1014;
    /**#@-*/

    /**
     * Db config key
     */
    public const KEY_DB = 'db';

    /**
     * Array Key for encryption key in deployment config file
     */
    public const KEY_ENCRYPTION_KEY = 'key';

    /**
     * Resource config key
     */
    public const KEY_RESOURCE = 'resource';

    /**
     * Key for modules
     */
    public const KEY_MODULES = 'modules';

    /**
     * Size of random string generated for store's encryption key
     * phpcs:disable
     */
    public const STORE_KEY_RANDOM_STRING_SIZE = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES;
    //phpcs:enable
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Backend\Setup\ConfigOptionsList as BackendConfig;
use Magento\Setup\Model\ConfigOptionsList as SetupConfig;

class DeploymentConfigMapper
{
    /**#@+
     * Parameters for setup tool
     */
    const KEY_DATE    = 'date';
    const KEY_DB_HOST = 'db_host';
    const KEY_DB_NAME = 'db_name';
    const KEY_DB_USER = 'db_user';
    const KEY_DB_PASS = 'db_pass';
    const KEY_DB_PREFIX = 'db_prefix';
    const KEY_DB_MODEL = 'db_model';
    const KEY_DB_INIT_STATEMENTS = 'db_init_statements';
    const KEY_SESSION_SAVE = 'session_save';
    const KEY_BACKEND_FRONTNAME = 'backend_frontname';
    const KEY_ENCRYPTION_KEY = 'key';
    /**#@-*/

    /**
     * Maps install parameter to array keys in deployment config file
     *
     * @var array
     */
    public static $paramMap = [
        self::KEY_DATE => SetupConfig::KEY_DATE,
        self::KEY_DB_HOST => SetupConfig::KEY_HOST,
        self::KEY_DB_NAME => SetupConfig::KEY_NAME,
        self::KEY_DB_USER => SetupConfig::KEY_USER,
        self::KEY_DB_PASS => SetupConfig::KEY_PASS,
        self::KEY_DB_PREFIX => SetupConfig::KEY_PREFIX,
        self::KEY_DB_MODEL => SetupConfig::KEY_MODEL,
        self::KEY_DB_INIT_STATEMENTS => SetupConfig::KEY_INIT_STATEMENTS,
        self::KEY_SESSION_SAVE => SetupConfig::KEY_SAVE,
        self::KEY_BACKEND_FRONTNAME => BackendConfig::KEY_FRONTNAME,
        self::KEY_ENCRYPTION_KEY => SetupConfig::KEY_ENCRYPTION_KEY,
    ];
}

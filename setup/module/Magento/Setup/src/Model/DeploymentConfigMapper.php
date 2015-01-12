<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig\BackendConfig;
use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\DeploymentConfig\EncryptConfig;
use Magento\Framework\App\DeploymentConfig\InstallConfig;
use Magento\Framework\App\DeploymentConfig\SessionConfig;

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
        self::KEY_DATE => InstallConfig::KEY_DATE,
        self::KEY_DB_HOST => DbConfig::KEY_HOST,
        self::KEY_DB_NAME => DbConfig::KEY_NAME,
        self::KEY_DB_USER => DbConfig::KEY_USER,
        self::KEY_DB_PASS => DbConfig::KEY_PASS,
        self::KEY_DB_PREFIX => DbConfig::KEY_PREFIX,
        self::KEY_DB_MODEL => DbConfig::KEY_MODEL,
        self::KEY_DB_INIT_STATEMENTS => DbConfig::KEY_INIT_STATEMENTS,
        self::KEY_SESSION_SAVE => SessionConfig::KEY_SAVE,
        self::KEY_BACKEND_FRONTNAME => BackendConfig::KEY_FRONTNAME,
        self::KEY_ENCRYPTION_KEY => EncryptConfig::KEY_ENCRYPTION_KEY,
    ];
}

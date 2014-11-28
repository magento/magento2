<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Module\Setup;

use Magento\Framework\App\DeploymentConfig\BackendConfig;
use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\DeploymentConfig\EncryptConfig;
use Magento\Framework\App\DeploymentConfig\InstallConfig;
use Magento\Framework\App\DeploymentConfig\SessionConfig;

class ConfigMapper
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

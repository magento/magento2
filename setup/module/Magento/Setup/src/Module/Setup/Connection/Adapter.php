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
namespace Magento\Setup\Module\Setup\Connection;

use Magento\Setup\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Setup\Module\Setup\Config;

class Adapter implements AdapterInterface
{
    /**
     * Get connection
     *
     * @param array $config
     * @return \Magento\Setup\Framework\DB\Adapter\AdapterInterface|null
     */
    public function getConnection(array $config = array())
    {
        return new Mysql(
            [
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname=" . $config[Config::KEY_DB_NAME] . ";host=" . $config[Config::KEY_DB_HOST],
                'username' => $config[Config::KEY_DB_USER],
                'password' => isset($config[Config::KEY_DB_PASS]) ? $config[Config::KEY_DB_PASS] : null,
                'driver_options' => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"]
            ]
        );
    }
}

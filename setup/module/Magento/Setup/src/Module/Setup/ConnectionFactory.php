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

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

class ConnectionFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create DB adapter object
     *
     * @param \ArrayObject|array $config
     * @return Mysql
     */
    public function create($config)
    {
        $config = [
            'driver' => 'Pdo',
            'dbname' => $config[Config::KEY_DB_NAME],
            'host' => $config[Config::KEY_DB_HOST],
            'username' => $config[Config::KEY_DB_USER],
            'password' => isset($config[Config::KEY_DB_PASS]) ? $config[Config::KEY_DB_PASS] : null,
            'driver_options' => [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"]
        ];
        return new Mysql(
            $this->serviceLocator->get('Magento\Framework\Filesystem'),
            $this->serviceLocator->get('Magento\Framework\Stdlib\String'),
            $this->serviceLocator->get('Magento\Framework\Stdlib\DateTime'),
            $config
        );
    }
}

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

namespace Magento\Framework\Model\Resource\Type\Db\Pdo;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConnection()
    {
        $db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()->getApplication()->getDbInstance();
        $config = [
            'profiler' => [
                'class' => '\Magento\Framework\DB\Profiler',
                'enabled' => true,
            ],
            'type' => 'pdo_mysql',
            'host' => $db->getHost(),
            'username' => $db->getUser(),
            'password' => $db->getPassword(),
            'dbname' => $db->getSchema(),
            'active' => true,
        ];
        /** @var \Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql $object */
        $object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql',
            ['config' => $config]
        );

        $connection = $object->getConnection(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\DB\LoggerInterface')
        );
        $this->assertInstanceOf('\Magento\Framework\DB\Adapter\Pdo\Mysql', $connection);
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('\Magento\Framework\DB\Profiler', $profiler);
    }
}

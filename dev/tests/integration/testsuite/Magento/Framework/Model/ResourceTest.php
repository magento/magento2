<?php
/**
 * Test for \Magento\Framework\Model\Resource
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Model;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\Resource');
    }

    public function testGetTableName()
    {
        $tablePrefix = 'prefix_';
        $tableSuffix = 'suffix';
        $tableNameOrig = 'store_website';

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Resource',
            ['tablePrefix' => 'prefix_']
        );

        $tableName = $this->_model->getTableName([$tableNameOrig, $tableSuffix]);
        $this->assertContains($tablePrefix, $tableName);
        $this->assertContains($tableSuffix, $tableName);
        $this->assertContains($tableNameOrig, $tableName);
    }

    /**
     * Init profiler during creation of DB connect
     */
    public function testProfilerInit()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Zend_Db_Adapter_Abstract $connection */
        $connection = $objectManager->create(
            'Magento\TestFramework\Db\Adapter\Mysql',
            [
                'config' => [
                    'profiler' => [
                        'class' => 'Magento\Framework\Model\Resource\Db\Profiler',
                        'enabled' => 'true',
                    ],
                    'username' => 'username',
                    'password' => 'password',
                    'host' => 'host',
                    'type' => 'type',
                    'dbname' => 'dbname',
                ]
            ]
        );

        /** @var \Magento\Framework\Model\Resource\Db\Profiler $profiler */
        $profiler = $connection->getProfiler();

        $this->assertInstanceOf('Magento\Framework\Model\Resource\Db\Profiler', $profiler);
        $this->assertTrue($profiler->getEnabled());
    }
}

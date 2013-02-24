<?php
/**
 * Test for Mage_Core_Model_Resource
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_ResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Resource');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @magentoConfigFixture global/resources/db/table_prefix prefix_
     */
    public function testGetTableName()
    {
        $tablePrefix = 'prefix_';
        $tableSuffix = 'suffix';
        $tableNameOrig = 'core_website';

        $tableName = $this->_model->getTableName(array($tableNameOrig, $tableSuffix));
        $this->assertContains($tablePrefix, $tableName);
        $this->assertContains($tableSuffix, $tableName);
        $this->assertContains($tableNameOrig, $tableName);
    }

    /**
     * Init profiler during creation of DB connect
     */
    public function testProfilerInit()
    {
        $connReadConfig = Mage::getSingleton('Mage_Core_Model_Config_Resource')
            ->getResourceConnectionConfig('core_read');
        $profilerConfig = $connReadConfig->addChild('profiler');
        $profilerConfig->addChild('class', 'Mage_Core_Model_Resource_Db_Profiler');
        $profilerConfig->addChild('enabled', 'true');

        /** @var Zend_Db_Adapter_Abstract $connection */
        $connection = $this->_model->getConnection('core_read');
        /** @var Mage_Core_Model_Resource_Db_Profiler $profiler */
        $profiler = $connection->getProfiler();

        $this->assertInstanceOf('Mage_Core_Model_Resource_Db_Profiler', $profiler);
        $this->assertTrue($profiler->getEnabled());
        $this->assertAttributeEquals((string)$connReadConfig->host, '_host', $profiler);
        $this->assertAttributeEquals((string)$connReadConfig->type, '_type', $profiler);
    }
}

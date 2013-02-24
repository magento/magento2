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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Config_ResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Resource
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    protected function setUp()
    {
        $this->_configMock = new Mage_Core_Model_Config_Base('
        <config>
            <global>
                <resources>
                    <default_setup>
                        <connection>
                            <type>pdo_mysql</type>
                            <model>mysql4</model>
                        </connection>
                    </default_setup>
                    <default_read>
                        <connection>
                            <use>default_setup</use>
                        </connection>
                    </default_read>
                    <core_setup>
                        <connection>
                            <use>default_setup</use>
                        </connection>
                    </core_setup>
                    <db>
                        <table_prefix>some_prefix_</table_prefix>
                    </db>
                </resources>
                <resource>
                    <connection>
                        <types>
                            <pdo_mysql>Mysql_Config</pdo_mysql>
                        </types>
                    </connection>
                </resource>
            </global>
        </config>
        ');
        $this->_model = new Mage_Core_Model_Config_Resource($this->_configMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_configMock);
    }

    public function testGetResourceConfig()
    {
        $resourceConfig = $this->_model->getResourceConfig('default_read');
        $this->assertEquals('default_setup', (string) $resourceConfig->connection->use);
    }

    public function testGetResourceConnectionConfig()
    {
        $resourceConfig = $this->_model->getResourceConnectionConfig('default_setup');
        $this->assertEquals('pdo_mysql', (string) $resourceConfig->type);
        $this->assertEquals('mysql4', (string) $resourceConfig->model);
    }

    public function testGetResourceConnectionConfigUsesInheritance()
    {
        $resourceConfig = $this->_model->getResourceConnectionConfig('default_read');
        $this->assertEquals('pdo_mysql', (string) $resourceConfig->type);
        $this->assertEquals('mysql4', (string) $resourceConfig->model);
    }

    public function testGetTablePrefix()
    {
        $this->assertEquals('some_prefix_', $this->_model->getTablePrefix());
    }

    public function testGetResourceTypeConfig()
    {
        $this->assertEquals('Mysql_Config', $this->_model->getResourceTypeConfig('pdo_mysql'));
    }

    public function testGetResourceConnectionModel()
    {
        $this->assertEquals('mysql4', $this->_model->getResourceConnectionModel('core'));
    }
}

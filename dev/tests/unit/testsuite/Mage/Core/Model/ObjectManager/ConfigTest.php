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
class Mage_Core_Model_ObjectManager_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_ObjectManager_Config
     */
    protected $_model;

    protected function setUp()
    {
        $params = array(
        );
        $this->_model = new Mage_Core_Model_ObjectManager_Config($params);
    }

    public function testConfigureInitializedObjectManager()
    {
        $configuration = $this->getMock('stdClass', array('asArray'));
        $configuration->expects($this->any())->method('asArray')->will($this->returnValue(array('configuratorClass')));
        $configMock = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);
        $configMock->expects($this->any())->method('getNode')->with($this->stringStartsWith('global'))
            ->will($this->returnValue($configuration));
        $objectManagerMock = $this->getMock('Magento_ObjectManager');
        $objectManagerMock->expects($this->exactly(2))->method('setConfiguration');
        $objectManagerMock->expects($this->once())->method('get')->with('Mage_Core_Model_Config_Primary')
            -> will($this->returnValue($configMock));
        $configuratorMock = $this->getMock('Magento_ObjectManager_Configuration');
        $configuratorMock->expects($this->once())->method('configure')->with($objectManagerMock);
        $objectManagerMock->expects($this->once())->method('create')->with('configuratorClass')
            ->will($this->returnValue($configuratorMock));
        $this->_model->configure($objectManagerMock);
    }
}

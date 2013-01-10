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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config
     */
    protected $_model;

    /**
     * @param mixed $data
     * @param array $map
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($data, $map)
    {
        //TODO: We should not use mocks in integration tests
        /** @var Magento_ObjectManager_Zend|PHPUnit_Framework_MockObject_MockObject $objectManagerMock */
        $objectManagerMock = $this->getMock('Magento_ObjectManager_Zend', array('create', 'get'), array(), '', false);
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap(array(
                $map,
                array('Mage_Core_Model_Config_Base', array(), true,  new Mage_Core_Model_Config_Base())
            )));

        $this->_model = new Mage_Core_Model_Config($objectManagerMock, $data);
        $this->assertInstanceOf('Mage_Core_Model_Config_Options', $this->_model->getOptions());
    }

    /**
     * @return array
     */
    public function constructorDataProvider()
    {
        $simpleXml = new Varien_Simplexml_Element('<body></body>');
        return array(
            array(
                'data' => null,
                'map' => array('Mage_Core_Model_Config_Options', array('data' => array(null)), true,
                    new Mage_Core_Model_Config_Options())
            ),
            array(
                'data' => array(),
                'map' => array('Mage_Core_Model_Config_Options', array('data' => array()), true,
                    new Mage_Core_Model_Config_Options())
            ),
            array('data' => $simpleXml,
                'map' => array('Mage_Core_Model_Config_Options', array('data' => array($simpleXml)), true,
                    new Mage_Core_Model_Config_Options())),
        );
    }
}

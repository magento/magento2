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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config
     */
    protected $_model;

    /**
     * @dataProvider constructorDataProvider
     * @param array|Varien_Simplexml_Element $data
     */
    public function testConstructor($data)
    {
        /** @var $objectManagerMock Magento_ObjectManager_Zend */
        $objectManagerMock = $this->getMock('Magento_ObjectManager_Zend', array('create', 'get'), array(), '', false);
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(array($this, 'getInstance')));

        $this->_model = new Mage_Core_Model_Config($objectManagerMock, $data);
        $this->assertInstanceOf('Mage_Core_Model_Config_Options', $this->_model->getOptions());
    }

    public function constructorDataProvider()
    {
        return array(
            array('data' => null),
            array('data' => array()),
            array('data' => new Varien_Simplexml_Element('<body></body>')),
        );
    }

    /**
     * Callback to use instead Magento_ObjectManager_Zend::create
     *
     * @param string $className
     * @param array $params
     * @return string
     */
    public function getInstance($className, $params = array())
    {
        return new $className($params);
    }
}
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
namespace Magento\GoogleShopping\Model;

class AttributeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get object manager mock
     *
     * @return \Magento\Framework\ObjectManager
     */
    protected function _createObjectManager()
    {
        return $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->setMethods(array('create'))
            ->getMockForAbstractClass();
    }

    /**
     * Get helper mock
     *
     * @return \Magento\GoogleShopping\Helper\Data
     */
    protected function _createGsData()
    {
        return $this->getMockBuilder(
            'Magento\GoogleShopping\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            null
        )->getMock();
    }

    /**
     * Get default attribute mock
     *
     * @return \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
     */
    protected function _createDefaultAttribute()
    {
        return $this->getMockBuilder(
            'Magento\GoogleShopping\Model\Attribute\DefaultAttribute'
        )->disableOriginalConstructor()->setMethods(
            array('__wakeup')
        )->getMock();
    }

    /**
     * @param string $name
     * @param string $expected
     * @dataProvider createAttributeDataProvider
     */
    public function testCreateAttribute($name, $expected)
    {
        $objectManager = $this->_createObjectManager();
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\GoogleShopping\Model\Attribute\\' . $expected)
        )->will(
            $this->returnValue($this->_createDefaultAttribute())
        );
        $attributeFactory = new \Magento\GoogleShopping\Model\AttributeFactory(
            $objectManager,
            $this->_createGsData(),
            new \Magento\Framework\Stdlib\String()
        );
        $attribute = $attributeFactory->createAttribute($name);
        $this->assertEquals($name, $attribute->getName());
    }

    public function createAttributeDataProvider()
    {
        return array(
            array('name', 'Name'),
            array('first_second', 'First_Second'),
            array('first_second_third', 'First_Second_Third')
        );
    }

    /**
     * @param bool $throwException
     * @dataProvider createAttributeDefaultDataProvider
     */
    public function testCreateAttributeDefault($throwException)
    {
        $objectManager = $this->_createObjectManager();
        $objectManager->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\GoogleShopping\Model\Attribute\Name')
        )->will(
            $throwException ? $this->throwException(new \Exception()) : $this->returnValue(false)
        );
        $objectManager->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\GoogleShopping\Model\Attribute\DefaultAttribute')
        )->will(
            $this->returnValue($this->_createDefaultAttribute())
        );
        $attributeFactory = new \Magento\GoogleShopping\Model\AttributeFactory(
            $objectManager,
            $this->_createGsData(),
            new \Magento\Framework\Stdlib\String()
        );
        $attribute = $attributeFactory->createAttribute('name');
        $this->assertEquals('name', $attribute->getName());
    }

    public function createAttributeDefaultDataProvider()
    {
        return array(array(true), array(false));
    }

    public function testCreate()
    {
        $objectManager = $this->_createObjectManager();
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\GoogleShopping\Model\Attribute'
        )->will(
            $this->returnValue('some value')
        );
        $attributeFactory = new \Magento\GoogleShopping\Model\AttributeFactory(
            $objectManager,
            $this->_createGsData(),
            new \Magento\Framework\Stdlib\String()
        );
        $attribute = $attributeFactory->create();
        $this->assertEquals('some value', $attribute);
    }
}

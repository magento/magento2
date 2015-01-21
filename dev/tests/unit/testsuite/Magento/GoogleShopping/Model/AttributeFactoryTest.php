<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

class AttributeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get object manager mock
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function _createObjectManager()
    {
        return $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods(['create'])
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
            ['__wakeup']
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
        return [
            ['name', 'Name'],
            ['first_second', 'First_Second'],
            ['first_second_third', 'First_Second_Third']
        ];
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
        return [[true], [false]];
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

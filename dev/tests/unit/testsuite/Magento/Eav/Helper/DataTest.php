<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeConfig;

    /**
     * Initialize helper
     */
    protected function setUp()
    {
        $context = $this->getMock('\Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->attributeConfig = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Config', [], [], '', false);
        $scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $eavConfig = $this->getMock('\Magento\Eav\Model\Config', [], [], '', false);
        $this->_helper = new Data($context, $this->attributeConfig, $scopeConfig, $eavConfig);
        $this->_eavConfig = $eavConfig;
    }

    public function testGetAttributeMetadata()
    {
        $attribute = new \Magento\Framework\Object([
            'entity_type_id' => '1',
            'attribute_id'   => '2',
            'backend'        => new \Magento\Framework\Object(['table' => 'customer_entity_varchar']),
            'backend_type'   => 'varchar',
        ]);
        $this->_eavConfig->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $result = $this->_helper->getAttributeMetadata('customer', 'lastname');
        $expected = [
            'entity_type_id' => '1',
            'attribute_id' => '2',
            'attribute_table' => 'customer_entity_varchar',
            'backend_type' => 'varchar',
        ];

        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $expected, 'Attribute metadata with key "' . $key . '" not found.');
            $this->assertEquals(
                $expected[$key],
                $value,
                'Attribute metadata with key "' . $key . '" has invalid value.'
            );
        }
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getFrontendClasses
     * @covers \Magento\Eav\Helper\Data::_getDefaultFrontendClasses
     */
    public function testGetFrontendClasses()
    {
        $result = $this->_helper->getFrontendClasses('someNonExistedClass');
        $this->assertTrue(count($result) > 1);
        $this->assertContains(['value' => '', 'label' => 'None'], $result);
        $this->assertContains(['value' => 'validate-number', 'label' => 'Decimal Number'], $result);
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsNoEntityCode()
    {
        $this->attributeConfig->expects($this->never())->method('getEntityAttributesLockedFields');
        $this->assertEquals([], $this->_helper->getAttributeLockedFields(''));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsNonCachedLockedFiled()
    {
        $lockedFields = ['lockedField1', 'lockedField2'];

        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->will($this->returnValue($lockedFields));
        $this->assertEquals($lockedFields, $this->_helper->getAttributeLockedFields('entityTypeCode'));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsCachedLockedFiled()
    {
        $lockedFields = ['lockedField1', 'lockedField2'];

        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->will($this->returnValue($lockedFields));

        $this->_helper->getAttributeLockedFields('entityTypeCode');
        $this->assertEquals($lockedFields, $this->_helper->getAttributeLockedFields('entityTypeCode'));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsNoLockedFields()
    {
        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->will($this->returnValue([]));

        $this->assertEquals([], $this->_helper->getAttributeLockedFields('entityTypeCode'));
    }
}

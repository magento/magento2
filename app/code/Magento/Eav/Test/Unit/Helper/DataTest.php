<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Helper;

use Magento\Store\Model\ScopeInterface;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * Initialize helper
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->attributeConfig = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Config', [], [], '', false);
        $this->eavConfig = $this->getMock('\Magento\Eav\Model\Config', [], [], '', false);
        $this->context = $this->getMock('\Magento\Framework\App\Helper\Context', ['getScopeConfig'], [], '', false);

        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->helper = $objectManager->getObject(
            '\Magento\Eav\Helper\Data',
            [
                'attributeConfig' => $this->attributeConfig,
                'eavConfig' => $this->eavConfig,
                'context' => $this->context,
            ]
        );
    }

    public function testGetAttributeMetadata()
    {
        $attribute = new \Magento\Framework\DataObject([
            'entity_type_id' => '1',
            'attribute_id'   => '2',
            'backend'        => new \Magento\Framework\DataObject(['table' => 'customer_entity_varchar']),
            'backend_type'   => 'varchar',
        ]);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $result = $this->helper->getAttributeMetadata('customer', 'lastname');
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
        $result = $this->helper->getFrontendClasses('someNonExistedClass');
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
        $this->assertEquals([], $this->helper->getAttributeLockedFields(''));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsNonCachedLockedFiled()
    {
        $lockedFields = ['lockedField1', 'lockedField2'];

        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->will($this->returnValue($lockedFields));
        $this->assertEquals($lockedFields, $this->helper->getAttributeLockedFields('entityTypeCode'));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsCachedLockedFiled()
    {
        $lockedFields = ['lockedField1', 'lockedField2'];

        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->will($this->returnValue($lockedFields));

        $this->helper->getAttributeLockedFields('entityTypeCode');
        $this->assertEquals($lockedFields, $this->helper->getAttributeLockedFields('entityTypeCode'));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsNoLockedFields()
    {
        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->will($this->returnValue([]));

        $this->assertEquals([], $this->helper->getAttributeLockedFields('entityTypeCode'));
    }

    public function testGetInputTypesValidatorData()
    {
        $configValue = 'config_value';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Eav\Helper\Data::XML_PATH_VALIDATOR_DATA_INPUT_TYPES, ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $this->assertEquals($configValue, $this->helper->getInputTypesValidatorData());
    }
}

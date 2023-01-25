<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Helper;

use Magento\Eav\Helper\Data;
use Magento\Eav\Model\Entity\Attribute\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var MockObject
     */
    protected $eavConfig;

    /**
     * @var MockObject
     */
    protected $attributeConfig;

    /**
     * @var MockObject
     */
    protected $context;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * Initialize helper
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->attributeConfig = $this->createMock(Config::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->context = $this->createPartialMock(Context::class, ['getScopeConfig']);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->helper = $objectManager->getObject(
            Data::class,
            [
                'attributeConfig' => $this->attributeConfig,
                'eavConfig' => $this->eavConfig,
                'context' => $this->context,
            ]
        );
    }

    public function testGetAttributeMetadata()
    {
        $attribute = new DataObject([
            'entity_type_id' => '1',
            'attribute_id'   => '2',
            'backend'        => new DataObject(['table' => 'customer_entity_varchar']),
            'backend_type'   => 'varchar',
        ]);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->willReturn($attribute);

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
        $this->assertGreaterThan(1, count($result));

        $result = array_map(function ($item) {
            if ($item['label'] instanceof Phrase) {
                $item['label'] = $item['label']->getText();
            }

            return $item;
        }, $result);

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
            ->with('entityTypeCode')->willReturn($lockedFields);
        $this->assertEquals($lockedFields, $this->helper->getAttributeLockedFields('entityTypeCode'));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsCachedLockedFiled()
    {
        $lockedFields = ['lockedField1', 'lockedField2'];

        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->willReturn($lockedFields);

        $this->helper->getAttributeLockedFields('entityTypeCode');
        $this->assertEquals($lockedFields, $this->helper->getAttributeLockedFields('entityTypeCode'));
    }

    /**
     * @covers \Magento\Eav\Helper\Data::getAttributeLockedFields
     */
    public function testGetAttributeLockedFieldsNoLockedFields()
    {
        $this->attributeConfig->expects($this->once())->method('getEntityAttributesLockedFields')
            ->with('entityTypeCode')->willReturn([]);

        $this->assertEquals([], $this->helper->getAttributeLockedFields('entityTypeCode'));
    }

    public function testGetInputTypesValidatorData()
    {
        $configValue = 'config_value';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::XML_PATH_VALIDATOR_DATA_INPUT_TYPES, ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $this->assertEquals($configValue, $this->helper->getInputTypesValidatorData());
    }
}

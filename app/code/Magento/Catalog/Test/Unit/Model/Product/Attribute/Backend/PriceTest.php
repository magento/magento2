<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

/**
 * Class PriceTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Price
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\Attribute\ScopeOverriddenValue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeOverriddenValue;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $localeFormat = $objectHelper->getObject(\Magento\Framework\Locale\Format::class);
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->scopeOverriddenValue = $this->getMockBuilder(
            \Magento\Catalog\Model\Attribute\ScopeOverriddenValue::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
            [
                'localeFormat' => $localeFormat,
                'storeManager' => $this->storeManager,
                'scopeOverriddenValue' => $this->scopeOverriddenValue
            ]
        );

        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['getAttributeCode', 'isScopeWebsite'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model->setAttribute($this->attribute);
    }

    /**
     * Tests for the cases that expect to pass validation
     *
     * @dataProvider dataProviderValidate
     */
    public function testValidate($value)
    {
        $object = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $object->expects($this->once())->method('getData')->willReturn($value);

        $this->assertTrue($this->model->validate($object));
    }

    /**
     * @return array
     */
    public function dataProviderValidate()
    {
        return [
            'US simple' => ['1234.56'],
            'US full'   => ['123,456.78'],
            'Brazil'    => ['123.456,78'],
            'India'     => ['1,23,456.78'],
            'Lebanon'   => ['1 234'],
            'zero'      => ['0.00'],
            'NaN becomes zero' => ['kiwi'],
        ];
    }

    /**
     * Tests for the cases that expect to fail validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider dataProviderValidateForFailure
     */
    public function testValidateForFailure($value)
    {
        $object = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $object->expects($this->once())->method('getData')->willReturn($value);

        $this->model->validate($object);
        $this->fail('Expected the following value to NOT validate: ' . $value);
    }

    /**
     * @return array
     */
    public function dataProviderValidateForFailure()
    {
        return [
            'negative US simple' => ['-1234.56'],
            'negative US full'   => ['-123,456.78'],
            'negative Brazil'    => ['-123.456,78'],
            'negative India'     => ['-1,23,456.78'],
            'negative Lebanon'   => ['-1 234'],
        ];
    }

    public function testAfterSaveWithDifferentStores()
    {
        $newPrice = '9.99';
        $attributeCode = 'price';
        $defaultStoreId = 0;
        $websiteStoreIds = [1, 2, 3];
        $object = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()->getMock();
        $object->expects($this->any())->method('getData')->with($attributeCode)->willReturn($newPrice);
        $object->expects($this->any())->method('getOrigData')->with($attributeCode)->willReturn('7.77');
        $object->expects($this->any())->method('getStoreId')->willReturn($defaultStoreId);
        $object->expects($this->any())->method('getWebsiteStoreIds')->willReturn($websiteStoreIds);
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->attribute->expects($this->any())->method('isScopeWebsite')
            ->willReturn(\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE);

        $object->expects($this->any())->method('addAttributeUpdate')->withConsecutive(
            [
                $this->equalTo($attributeCode),
                $this->equalTo($newPrice),
                $this->equalTo($websiteStoreIds[0])
            ],
            [
                $this->equalTo($attributeCode),
                $this->equalTo($newPrice),
                $this->equalTo($websiteStoreIds[1])
            ],
            [
                $this->equalTo($attributeCode),
                $this->equalTo($newPrice),
                $this->equalTo($websiteStoreIds[2])
            ]
        );
        $this->assertEquals($this->model, $this->model->afterSave($object));
    }

    public function testAfterSaveWithOldPrice()
    {
        $attributeCode = 'price';

        $object = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()->getMock();
        $object->expects($this->any())->method('getData')->with($attributeCode)->willReturn('7.77');
        $object->expects($this->any())->method('getOrigData')->with($attributeCode)->willReturn('7.77');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->attribute->expects($this->any())->method('getIsGlobal')
            ->willReturn(\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE);

        $object->expects($this->never())->method('addAttributeUpdate');
        $this->assertEquals($this->model, $this->model->afterSave($object));
    }

    public function testAfterSaveWithGlobalPrice()
    {
        $attributeCode = 'price';

        $object = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()->getMock();
        $object->expects($this->any())->method('getData')->with($attributeCode)->willReturn('9.99');
        $object->expects($this->any())->method('getOrigData')->with($attributeCode)->willReturn('7.77');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->attribute->expects($this->any())->method('getIsGlobal')
            ->willReturn(\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL);

        $object->expects($this->never())->method('addAttributeUpdate');
        $this->assertEquals($this->model, $this->model->afterSave($object));
    }
}

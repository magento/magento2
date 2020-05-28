<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    private $model;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attribute;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /** @var  CurrencyFactory|MockObject */
    private $currencyFactory;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);
        $localeFormat = $objectHelper->getObject(Format::class);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectHelper->getObject(
            Price::class,
            [
                'localeFormat' => $localeFormat,
                'storeManager' => $this->storeManager,
                'currencyFactory' => $this->currencyFactory
            ]
        );
        $this->attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getAttributeCode', 'isScopeWebsite', 'getIsGlobal'])
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
        $object = $this->createMock(Product::class);
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
     * @dataProvider dataProviderValidateForFailure
     */
    public function testValidateForFailure($value)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $object = $this->createMock(Product::class);
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
        $allStoreIds = [1, 2, 3];
        $object = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->any())->method('getData')->with($attributeCode)->willReturn($newPrice);
        $object->expects($this->any())->method('getOrigData')->with($attributeCode)->willReturn('7.77');
        $object->expects($this->any())->method('getStoreId')->willReturn($defaultStoreId);
        $object->expects($this->never())->method('getStoreIds');
        $object->expects($this->never())->method('getWebsiteStoreIds');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->attribute->expects($this->any())->method('isScopeWebsite')
            ->willReturn(ScopedAttributeInterface::SCOPE_WEBSITE);
        $this->storeManager->expects($this->never())->method('getStore');

        $object->expects($this->any())->method('addAttributeUpdate')->withConsecutive(
            [
                $this->equalTo($attributeCode),
                $this->equalTo($newPrice),
                $this->equalTo($allStoreIds[0])
            ],
            [
                $this->equalTo($attributeCode),
                $this->equalTo($newPrice),
                $this->equalTo($allStoreIds[1])
            ],
            [
                $this->equalTo($attributeCode),
                $this->equalTo($newPrice),
                $this->equalTo($allStoreIds[2])
            ]
        );
        $this->assertEquals($this->model, $this->model->afterSave($object));
    }

    public function testAfterSaveWithOldPrice()
    {
        $attributeCode = 'price';

        $object = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->any())->method('getData')->with($attributeCode)->willReturn('7.77');
        $object->expects($this->any())->method('getOrigData')->with($attributeCode)->willReturn('7.77');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->attribute->expects($this->any())->method('getIsGlobal')
            ->willReturn(ScopedAttributeInterface::SCOPE_WEBSITE);

        $object->expects($this->never())->method('addAttributeUpdate');
        $this->assertEquals($this->model, $this->model->afterSave($object));
    }

    public function testAfterSaveWithGlobalPrice()
    {
        $attributeCode = 'price';

        $object = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->any())->method('getData')->with($attributeCode)->willReturn('9.99');
        $object->expects($this->any())->method('getOrigData')->with($attributeCode)->willReturn('7.77');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->attribute->expects($this->any())->method('getIsGlobal')
            ->willReturn(ScopedAttributeInterface::SCOPE_GLOBAL);

        $object->expects($this->never())->method('addAttributeUpdate');
        $this->assertEquals($this->model, $this->model->afterSave($object));
    }
}

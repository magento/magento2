<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value as OptionValue;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for \Magento\Catalog\Model\Product\Option\Value class.
 */
class ValueTest extends TestCase
{
    /**
     * @var Value
     */
    private $model;

    /**
     * @var CustomOptionPriceCalculator|MockObject
     */
    private $customOptionPriceCalculatorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $mockedResource = $this->getMockedResource();
        $mockedCollectionFactory = $this->getMockedValueCollectionFactory();

        $this->customOptionPriceCalculatorMock = $this->createMock(
            CustomOptionPriceCalculator::class
        );

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Value::class,
            [
                'resource' => $mockedResource,
                'valueCollectionFactory' => $mockedCollectionFactory,
                'customOptionPriceCalculator' => $this->customOptionPriceCalculatorMock
            ]
        );
        $this->model->setOption($this->getMockedOption());
    }

    public function testSaveProduct()
    {
        $this->model->setValues([100])
            ->setData('option_type_id', -1)
            ->setDataChanges(false)
            ->isDeleted(false);
        $this->assertInstanceOf(Value::class, $this->model->saveValues());

        $this->model->setData('is_delete', 1)
            ->setData('option_type_id', 1)
            ->setValues([100]);
        $this->assertInstanceOf(Value::class, $this->model->saveValues());
    }

    public function testGetPrice()
    {
        $price = 1000.0;
        $this->model->setPrice($price);
        $this->model->setPriceType(Value::TYPE_PERCENT);
        $this->assertEquals($price, $this->model->getPrice(false));

        $percentPrice = 100.0;
        $this->customOptionPriceCalculatorMock->expects($this->atLeastOnce())
            ->method('getOptionPriceByPriceCode')
            ->willReturn($percentPrice);
        $this->assertEquals($percentPrice, $this->model->getPrice(true));
    }

    public function testGetValuesCollection()
    {
        $this->assertInstanceOf(
            Collection::class,
            $this->model->getValuesCollection($this->getMockedOption())
        );
    }

    public function testGetValuesByOption()
    {
        $this->assertInstanceOf(
            Collection::class,
            $this->model->getValuesByOption([1], 1, 1)
        );
    }

    public function testGetProduct()
    {
        $this->assertInstanceOf(Product::class, $this->model->getProduct());
    }

    public function testDuplicate()
    {
        $this->assertInstanceOf(Value::class, $this->model->duplicate(1, 1));
    }

    public function testDeleteValues()
    {
        $this->assertInstanceOf(Value::class, $this->model->deleteValues(1));
    }

    public function testDeleteValue()
    {
        $this->assertInstanceOf(Value::class, $this->model->deleteValue(1));
    }

    /**
     * @return CollectionFactory
     */
    private function getMockedValueCollectionFactory()
    {
        $mockedCollection = $this->getMockedValueCollection();

        $mock = $this->createPartialMock(CollectionFactory::class, ['create']);

        $mock->method('create')->willReturn($mockedCollection);

        return $mock;
    }

    /**
     * @return Collection
     */
    private function getMockedValueCollection()
    {
        $mock = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'getValuesByOption', 'getValues']
        );

        $mock->method('addFieldToFilter')->willReturn($mock);

        $mock->method('getValuesByOption')->willReturn($mock);

        $mock->method('getValues')->willReturn($mock);

        return $mock;
    }

    /**
     * @return Option
     */
    private function getMockedOption()
    {
        $mockedProduct = $this->getMockedProduct();

        $mock = $this->createMock(Option::class);

        $mock->method('getProduct')->willReturn($mockedProduct);

        return $mock;
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mock = $this->createPartialMock(Product::class, ['getPriceInfo']);

        $priceInfoMock = $this->createMock(PriceInfoInterface::class);

        $priceMock = $this->createMock(PriceInterface::class);

        $priceInfoMock->method('getPrice')->willReturn($priceMock);

        $mock->method('getPriceInfo')->willReturn($priceInfoMock);

        $priceMock->method('getValue')->willReturn(10);

        return $mock;
    }

    /**
     * @return OptionValue
     */
    private function getMockedResource()
    {
        $mock = $this->createPartialMock(
            OptionValue::class,
            [
                'duplicate', 'getIdFieldName', 'deleteValues', 'deleteValue', 'beginTransaction',
                'delete', 'commit', 'save', 'addCommitCallback'
            ]
        );

        $mock->expects($this->any())
            ->method('duplicate');

        $mock->expects($this->any())
            ->method('deleteValues');

        $mock->expects($this->any())
            ->method('deleteValue');

        $mock->expects($this->any())
            ->method('delete');

        $mock->expects($this->any())
            ->method('save');

        $mock->expects($this->any())
            ->method('commit');

        $mock->method('addCommitCallback')->willReturn($mock);

        $mock->expects($this->any())
            ->method('beginTransaction');

        $mock->method('getIdFieldName')->willReturn('testField');

        return $mock;
    }
}

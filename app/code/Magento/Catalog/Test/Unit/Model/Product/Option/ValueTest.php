<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory;
use Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator;

use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

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
     * @var CustomOptionPriceCalculator
     */
    private $customOptionPriceCalculatorMock;

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
                'customOptionPriceCalculator' => $this->customOptionPriceCalculatorMock,
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

        $mockBuilder =
            $this->getMockBuilder(CollectionFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->willReturn($mockedCollection);

        return $mock;
    }

    /**
     * @return Collection
     */
    private function getMockedValueCollection()
    {
        $mockBuilder = $this->getMockBuilder(
            Collection::class
        )->setMethods(['addFieldToFilter', 'getValuesByOption', 'getValues'])->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn($mock);

        $mock->expects($this->any())
            ->method('getValuesByOption')
            ->willReturn($mock);

        $mock->expects($this->any())
            ->method('getValues')
            ->willReturn($mock);

        return $mock;
    }

    /**
     * @return Option
     */
    private function getMockedOption()
    {
        $mockedProduct = $this->getMockedProduct();

        $mockBuilder = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getProduct')
            ->willReturn($mockedProduct);

        return $mock;
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mockBuilder = $this->getMockBuilder(Product::class)
            ->setMethods(['getPriceInfo'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $priceInfoMock = $this->getMockForAbstractClass(
            PriceInfoInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getPrice']
        );

        $priceMock = $this->getMockForAbstractClass(PriceInterface::class);

        $priceInfoMock->expects($this->any())->method('getPrice')->willReturn($priceMock);

        $mock->expects($this->any())->method('getPriceInfo')->willReturn($priceInfoMock);

        $priceMock->expects($this->any())->method('getValue')->willReturn(10);

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value
     */
    private function getMockedResource()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Option\Value::class)
            ->setMethods(
                [
                    'duplicate',
                    'getIdFieldName',
                    'deleteValues',
                    'deleteValue',
                    'beginTransaction',
                    'delete',
                    'commit',
                    'save',
                    'addCommitCallback',
                ]
            )
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

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

        $mock->expects($this->any())
            ->method('addCommitCallback')
            ->willReturn($mock);

        $mock->expects($this->any())
            ->method('beginTransaction');

        $mock->expects($this->any())
            ->method('getIdFieldName')
            ->willReturn('testField');

        return $mock;
    }
}

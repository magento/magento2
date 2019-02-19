<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use \Magento\Catalog\Model\Product\Option\Value;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Value
     */
    private $model;

    /**
     * @var \Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator
     */
    private $customOptionPriceCalculatorMock;

    protected function setUp()
    {
        $mockedResource = $this->getMockedResource();
        $mockedCollectionFactory = $this->getMockedValueCollectionFactory();

        $this->customOptionPriceCalculatorMock = $this->createMock(
            \Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator::class
        );

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Product\Option\Value::class,
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
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Option\Value::class, $this->model->saveValues());

        $this->model->setData('is_delete', 1)
            ->setData('option_type_id', 1)
            ->setValues([100]);
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Option\Value::class, $this->model->saveValues());
    }

    public function testGetPrice()
    {
        $price = 1000;
        $this->model->setPrice($price);
        $this->model->setPriceType(Value::TYPE_PERCENT);
        $this->assertEquals($price, $this->model->getPrice(false));

        $percentPice = 100;
        $this->customOptionPriceCalculatorMock->expects($this->atLeastOnce())
            ->method('getOptionPriceByPriceCode')
            ->willReturn($percentPice);
        $this->assertEquals($percentPice, $this->model->getPrice(true));
    }

    public function testGetValuesCollection()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection::class,
            $this->model->getValuesCollection($this->getMockedOption())
        );
    }

    public function testGetValuesByOption()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection::class,
            $this->model->getValuesByOption([1], 1, 1)
        );
    }

    public function testGetProduct()
    {
        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $this->model->getProduct());
    }

    public function testDuplicate()
    {
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Option\Value::class, $this->model->duplicate(1, 1));
    }

    public function testDeleteValues()
    {
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Option\Value::class, $this->model->deleteValues(1));
    }

    public function testDeleteValue()
    {
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Option\Value::class, $this->model->deleteValue(1));
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory
     */
    private function getMockedValueCollectionFactory()
    {
        $mockedCollection = $this->getMockedValueCollection();

        $mockBuilder =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($mockedCollection));

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    private function getMockedValueCollection()
    {
        $mockBuilder = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection::class
        )->setMethods(['addFieldToFilter', 'getValuesByOption', 'getValues'])->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('getValuesByOption')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($mock));

        return $mock;
    }

    /**
     * @return Option
     */
    private function getMockedOption()
    {
        $mockedProduct = $this->getMockedProduct();

        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($mockedProduct));

        return $mock;
    }

    /**
     * @return Product
     */
    private function getMockedProduct()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $priceInfoMock = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceInfoInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getPrice']
        );

        $priceMock = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Price\PriceInterface::class);

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
                    '__wakeup',
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
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('beginTransaction');

        $mock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('testField'));

        return $mock;
    }
}

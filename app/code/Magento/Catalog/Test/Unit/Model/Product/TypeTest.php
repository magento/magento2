<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Pool;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Catalog\Model\Product\Type\Price\Factory as PriceFactory;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\Product\Type\Virtual;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Pricing\PriceInfo\Factory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectHelper;

    /**
     * Product types config values
     *
     * @var array
     */
    protected $_productTypes = [
        'type_id_1' => ['label' => 'label_1'],
        'type_id_2' => ['label' => 'label_2'],
        'type_id_3' => [
            'label' => 'label_3',
            'model' => 'some_model',
            'composite' => 'some_type',
            'price_model' => 'some_model'
        ],
        'simple' => ['label' => 'label_4', 'composite' => false]
    ];

    /**
     * @var Type
     */
    protected $_model;

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testGetTypes(): void
    {
        $property = new \ReflectionProperty($this->_model, '_types');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->_model));
        $this->assertEquals($this->_productTypes, $this->_model->getTypes());
    }

    /**
     * @return void
     */
    public function testGetOptionArray(): void
    {
        $this->assertEquals($this->getOptionArray(), $this->_model->getOptionArray());
    }

    /**
     * @return void
     */
    public function testGetAllOptions(): void
    {
        $res[] = ['value' => '', 'label' => ''];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        $this->assertEquals($res, $this->_model->getAllOptions());
    }

    /**
     * @return void
     */
    public function testGetOptions(): void
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        $this->assertEquals($res, $this->_model->getOptions());
    }

    /**
     * @return void
     */
    public function testGetAllOption(): void
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        $this->assertEquals($options, $this->_model->getAllOption());
    }

    /**
     * @return void
     */
    public function testGetOptionText(): void
    {
        $options = $this->getOptionArray();
        $this->assertEquals($options['type_id_3'], $this->_model->getOptionText('type_id_3'));
        $this->assertEquals($options['type_id_1'], $this->_model->getOptionText('type_id_1'));
        $this->assertNotEquals($options['type_id_1'], $this->_model->getOptionText('simple'));
        $this->assertNull($this->_model->getOptionText('not_exist'));
    }

    /**
     * @return void
     */
    public function testGetCompositeTypes(): void
    {
        $property = new \ReflectionProperty($this->_model, '_compositeTypes');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->_model));

        $this->assertEquals(['type_id_3'], $this->_model->getCompositeTypes());
    }

    /**
     * @return void
     */
    public function testGetTypesByPriority(): void
    {
        $expected = [];
        $options = [];
        foreach ($this->_productTypes as $typeId => $type) {
            $type['label'] = __($type['label']);
            $options[$typeId] = $type;
        }

        $expected['simple'] = $options['simple'];
        $expected['type_id_2'] = $options['type_id_2'];
        $expected['type_id_1'] = $options['type_id_1'];
        $expected['type_id_3'] = $options['type_id_3'];

        $this->assertEquals($expected, $this->_model->getTypesByPriority());
    }

    /**
     * @return void
     */
    public function testGetPriceInfo(): void
    {
        $mockedProduct = $this->getMockedProduct();
        $expectedResult = PriceInfoInterface::class;
        $this->assertInstanceOf($expectedResult, $this->_model->getPriceInfo($mockedProduct));
    }

    /**
     * @return void
     */
    public function testFactory(): void
    {
        $mockedProduct = $this->getMockedProduct();

        $mockedProduct
            ->method('getTypeId')
            ->willReturnOnConsecutiveCalls('type_id_1', 'type_id_3');

        $this->assertInstanceOf(
            Simple::class,
            $this->_model->factory($mockedProduct)
        );
        $this->assertInstanceOf(
            Virtual::class,
            $this->_model->factory($mockedProduct)
        );
    }

    /**
     * @return void
     */
    public function testPriceFactory(): void
    {
        $this->assertInstanceOf(
            Price::class,
            $this->_model->priceFactory('type_id_1')
        );
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_objectHelper = new ObjectManager($this);
        $mockedPriceInfoFactory = $this->getMockedPriceInfoFactory();
        $mockedProductTypePool = $this->getMockedProductTypePool();
        $mockedConfig = $this->getMockedConfig();
        $mockedTypePriceFactory = $this->getMockedTypePriceFactory();

        $this->_model = $this->_objectHelper->getObject(
            Type::class,
            [
                'config' => $mockedConfig,
                'priceInfoFactory' => $mockedPriceInfoFactory,
                'productTypePool' => $mockedProductTypePool,
                'priceFactory' => $mockedTypePriceFactory
            ]
        );
    }

    /**
     * @return array
     */
    protected function getOptionArray(): array
    {
        $options = [];
        foreach ($this->_productTypes as $typeId => $type) {
            $options[$typeId] = __($type['label']);
        }
        return $options;
    }

    /**
     * @return Product|MockObject
     */
    private function getMockedProduct(): Product
    {
        $mockBuilder = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        return $mock;
    }

    /**
     * @return Factory|MockObject
     */
    private function getMockedPriceInfoFactory(): Factory
    {
        $mockedPriceInfoInterface = $this->getMockedPriceInfoInterface();

        $mockBuilder = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->willReturn($mockedPriceInfoInterface);

        return $mock;
    }

    /**
     * @return PriceInfoInterface|MockObject
     */
    private function getMockedPriceInfoInterface(): PriceInfoInterface
    {
        $mockBuilder = $this->getMockBuilder(PriceInfoInterface::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        return $mock;
    }

    /**
     * @return Pool|MockObject
     */
    private function getMockedProductTypePool(): Pool
    {
        $mockBuild = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get']);
        $mock = $mockBuild->getMock();

        $mock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['some_model', [], $this->getMockedProductTypeVirtual()],
                    [Simple::class, [], $this->getMockedProductTypeSimple()]
                ]
            );

        return $mock;
    }

    /**
     * @return Virtual|MockObject
     */
    private function getMockedProductTypeVirtual(): Virtual
    {
        $mockBuilder = $this->getMockBuilder(Virtual::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setConfig']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('setConfig');

        return $mock;
    }

    /**
     * @return Simple|MockObject
     */
    private function getMockedProductTypeSimple(): Simple
    {
        $mockBuilder = $this->getMockBuilder(Simple::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setConfig']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('setConfig');

        return $mock;
    }

    /**
     * @return ConfigInterface|MockObject
     */
    private function getMockedConfig(): ConfigInterface
    {
        $mockBuild = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAll']);
        $mock = $mockBuild->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getAll')
            ->willReturn($this->_productTypes);

        return $mock;
    }

    /**
     * @return PriceFactory|MockObject
     */
    private function getMockedTypePriceFactory(): PriceFactory
    {
        $mockBuild = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price\Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create']);
        $mock = $mockBuild->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    ['some_model', [], $this->getMockedProductTypePrice()],
                    [Price::class, [], $this->getMockedProductTypePrice()]
                ]
            );

        return $mock;
    }

    /**
     * @return Price|MockObject
     */
    private function getMockedProductTypePrice(): Price
    {
        $mockBuild = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor();
        $mock = $mockBuild->getMock();

        return $mock;
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;


/**
 * ProductType Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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
            'price_model' => 'some_model',
        ],
        'simple' => ['label' => 'label_4', 'composite' => false],
    ];

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_model;

    public function testGetTypes()
    {
        $property = new \ReflectionProperty($this->_model, '_types');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->_model));
        $this->assertEquals($this->_productTypes, $this->_model->getTypes());
    }

    public function testGetOptionArray()
    {
        $this->assertEquals($this->getOptionArray(), $this->_model->getOptionArray());
    }

    public function testGetAllOptions()
    {
        $res[] = ['value' => '', 'label' => ''];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        $this->assertEquals($res, $this->_model->getAllOptions());
    }

    public function testGetOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        $this->assertEquals($res, $this->_model->getOptions());
    }

    public function testGetAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        $this->assertEquals($options, $this->_model->getAllOption());
    }

    public function testGetOptionText()
    {
        $options = $this->getOptionArray();
        $this->assertEquals($options['type_id_3'], $this->_model->getOptionText('type_id_3'));
        $this->assertEquals($options['type_id_1'], $this->_model->getOptionText('type_id_1'));
        $this->assertNotEquals($options['type_id_1'], $this->_model->getOptionText('simple'));
        $this->assertNull($this->_model->getOptionText('not_exist'));
    }

    public function testGetCompositeTypes()
    {
        $property = new \ReflectionProperty($this->_model, '_compositeTypes');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->_model));

        $this->assertEquals(['type_id_3'], $this->_model->getCompositeTypes());
    }

    public function testGetTypesByPriority()
    {
        $expected = [];
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

    public function testGetPriceInfo()
    {
        $mockedProduct = $this->getMockedProduct();
        $expectedResult = \Magento\Framework\Pricing\PriceInfoInterface::class;
        $this->assertInstanceOf($expectedResult, $this->_model->getPriceInfo($mockedProduct));
    }

    public function testFactory()
    {
        $mockedProduct = $this->getMockedProduct();

        $mockedProduct->expects($this->at(0))
            ->method('getTypeId')
            ->will($this->returnValue('type_id_1'));
        $mockedProduct->expects($this->at(1))
            ->method('getTypeId')
            ->will($this->returnValue('type_id_3'));

        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Type\Simple::class,
            $this->_model->factory($mockedProduct)
        );
        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Type\Virtual::class,
            $this->_model->factory($mockedProduct)
        );
    }

    public function testPriceFactory()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Type\Price::class,
            $this->_model->priceFactory('type_id_1')
        );
    }

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $mockedPriceInfoFactory = $this->getMockedPriceInfoFactory();
        $mockedProductTypePool = $this->getMockedProductTypePool();
        $mockedConfig = $this->getMockedConfig();
        $mockedTypePriceFactory = $this->getMockedTypePriceFactory();

        $this->_model = $this->_objectHelper->getObject(
            \Magento\Catalog\Model\Product\Type::class,
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
    protected function getOptionArray()
    {
        $options = [];
        foreach ($this->_productTypes as $typeId => $type) {
            $options[$typeId] = __($type['label']);
        }
        return $options;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    private function getMockedProduct()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        return $mock;
    }

    /**
     * @return \Magento\Framework\Pricing\PriceInfo\Factory
     */
    private function getMockedPriceInfoFactory()
    {
        $mockedPriceInfoInterface = $this->getMockedPriceInfoInterface();

        $mockBuilder = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($mockedPriceInfoInterface));

        return $mock;
    }

    /**
     * @return \Magento\Framework\Pricing\PriceInfoInterface
     */
    private function getMockedPriceInfoInterface()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfoInterface::class)
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Type\Pool
     */
    private function getMockedProductTypePool()
    {
        $mockBuild = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Pool::class)
            ->disableOriginalConstructor()
            ->setMethods(['get']);
        $mock = $mockBuild->getMock();

        $mock->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['some_model', [], $this->getMockedProductTypeVirtual()],
                        [\Magento\Catalog\Model\Product\Type\Simple::class, [], $this->getMockedProductTypeSimple()],
                    ]
                )
            );

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Type\Virtual
     */
    private function getMockedProductTypeVirtual()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Virtual::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfig']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('setConfig');

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Type\Simple
     */
    private function getMockedProductTypeSimple()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Simple::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfig']);
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('setConfig');

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    private function getMockedConfig()
    {
        $mockBuild = $this->getMockBuilder(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAll']);
        $mock = $mockBuild->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue($this->_productTypes));

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Type\Price\Factory
     */
    private function getMockedTypePriceFactory()
    {
        $mockBuild = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create']);
        $mock = $mockBuild->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('create')
            ->will(
                $this->returnValueMap(
                    [
                        ['some_model', [], $this->getMockedProductTypePrice()],
                        [\Magento\Catalog\Model\Product\Type\Price::class, [], $this->getMockedProductTypePrice()],
                    ]
                )
            );

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Type\Price
     */
    private function getMockedProductTypePrice()
    {
        $mockBuild = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price::class)
            ->disableOriginalConstructor();
        $mock = $mockBuild->getMock();

        return $mock;
    }
}

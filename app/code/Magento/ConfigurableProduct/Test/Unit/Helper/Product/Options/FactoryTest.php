<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Helper\Product\Options;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class FactoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configurable|MockObject
     */
    private $configurable;

    /**
     * @var AttributeFactory|MockObject
     */
    private $attributeFactory;

    /**
     * @var OptionValueInterfaceFactory|MockObject
     */
    private $optionValueFactory;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->configurable = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['canUseAttribute'])
            ->getMock();

        $this->attributeFactory = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->optionValueFactory = $this->getMockBuilder(OptionValueInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productAttributeRepository = $this->getMock(ProductAttributeRepositoryInterface::class);

        $this->factory = new Factory(
            $this->configurable,
            $this->attributeFactory,
            $this->optionValueFactory,
            $this->productAttributeRepository
        );
    }

    /**
     * @covers \Magento\ConfigurableProduct\Helper\Product\Options\Factory::create
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided attribute can not be used with configurable product.
     */
    public function testCreateWithException()
    {
        $attributeId = 90;
        $data = [
            ['attribute_id' => $attributeId, 'values' => [
                ['value_index' => 12], ['value_index' => 13]
            ]]
        ];

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValues', 'getData'])
            ->getMock();

        $this->attributeFactory->expects(static::once())
            ->method('create')
            ->willReturn($attribute);

        $eavAttribute = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->with($attributeId)
            ->willReturn($eavAttribute);

        $this->configurable->expects(static::once())
            ->method('canUseAttribute')
            ->with($eavAttribute)
            ->willReturn(false);

        $this->factory->create($data);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Helper\Product\Options\Factory::create
     */
    public function testCreate()
    {
        $attributeId = 90;
        $valueIndex = 12;
        $item = ['attribute_id' => $attributeId, 'values' => [['value_index' => $valueIndex]]];
        $data = [$item];

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValues', 'setData', '__wakeup'])
            ->getMock();

        $this->attributeFactory->expects(static::once())
            ->method('create')
            ->willReturn($attribute);

        $eavAttribute = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->with($attributeId)
            ->willReturn($eavAttribute);

        $this->configurable->expects(static::once())
            ->method('canUseAttribute')
            ->with($eavAttribute)
            ->willReturn(true);

        $option = $this->getMock(OptionValueInterface::class);
        $option->expects(static::once())
            ->method('setValueIndex')
            ->with($valueIndex)
            ->willReturnSelf();
        $this->optionValueFactory->expects(static::once())
            ->method('create')
            ->willReturn($option);

        $attribute->expects(static::once())
            ->method('setData')
            ->with($item);

        $attribute->expects(static::once())
            ->method('setValues')
            ->with([$option]);

        $result = $this->factory->create($data);
        static::assertSame([$attribute], $result);
    }
}

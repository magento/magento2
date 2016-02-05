<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\OptionRepository;
use Magento\ConfigurableProduct\Model\Product\SaveHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SaveHandlerTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionRepository|MockObject
     */
    private $optionRepository;

    /**
     * @var ConfigurableFactory|MockObject
     */
    private $configurableFactory;

    /**
     * @var Configurable|MockObject
     */
    private $configurable;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->optionRepository = $this->getMockBuilder(OptionRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList', 'deleteById'])
            ->getMock();

        $this->initConfigurableFactoryMock();

        $this->productRepository = $this->getMock(ProductAttributeRepositoryInterface::class);

        $this->saveHandler = new SaveHandler(
            $this->optionRepository,
            $this->configurableFactory,
            $this->productRepository
        );
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecuteWithInvalidProductType()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects(static::never())
            ->method('getExtensionAttributes');

        $entity = $this->saveHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecuteWithEmptyExtensionAttributes()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);

        $extensionAttributes = $this->getMockBuilder(PaymentExtensionAttributes::class)
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([]);
        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn([]);

        $this->productRepository->expects(static::never())
            ->method('get');

        $entity = $this->saveHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecute()
    {
        $attributeId = 90;
        $sku = 'config-1';
        $id = 25;

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getSku', 'getData', 'getExtensionAttributes'])
            ->getMock();
        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);

        $extensionAttributes = $this->getMockBuilder(PaymentExtensionAttributes::class)
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeId', 'loadByProductAndAttribute'])
            ->getMock();
        $attribute->expects(static::once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $eavAttribute = $this->getMock(ProductAttributeInterface::class);
        $this->productRepository->expects(static::once())
            ->method('get')
            ->with($attributeId)
            ->willReturn($eavAttribute);
        $attribute->expects(static::once())
            ->method('loadByProductAndAttribute')
            ->with($product, $eavAttribute)
            ->willReturnSelf();

        $this->optionRepository->expects(static::once())
            ->method('save')
            ->with($sku, $attribute)
            ->willReturn($id);

        $product->expects(static::exactly(2))
            ->method('getSku')
            ->willReturn($sku);

        $option = $this->getMockForAbstractClass(OptionInterface::class);
        $option->expects(static::once())
            ->method('getId')
            ->willReturn($id);

        $list = [$option];
        $this->optionRepository->expects(static::once())
            ->method('getList')
            ->with($sku)
            ->willReturn($list);
        $this->optionRepository->expects(static::never())
            ->method('deleteById');

        $configurableAttributes = [
            $attribute
        ];
        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn($configurableAttributes);

        $configurableProductLinks = [1, 2, 3];
        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);

        $this->configurable->expects(static::once())
            ->method('saveProducts')
            ->with($product, $configurableProductLinks);

        $entity = $this->saveHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * Init mock object for configurable factory
     *
     * @return void
     */
    private function initConfigurableFactoryMock()
    {
        $this->configurable = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->configurableFactory = $this->getMockBuilder(ConfigurableFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->configurableFactory->expects(static::any())
            ->method('create')
            ->willReturn($this->configurable);
    }
}

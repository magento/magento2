<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\OptionRepository;
use Magento\ConfigurableProduct\Model\Product\SaveHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
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
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepository;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->optionRepository = $this->getMockBuilder(OptionRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'getList', 'deleteById'])
            ->getMock();

        $this->initConfigurableFactoryMock();

        $this->productRepository = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $this->saveHandler = new SaveHandler(
            $this->configurable,
            $this->optionRepository,
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
            ->onlyMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects(static::never())
            ->method('getExtensionAttributes');

        $entity = $this->saveHandler->execute($product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecuteWithEmptyExtensionAttributes()
    {
        $sku = 'test';
        $configurableProductLinks = [1, 2, 3];
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeId', 'getExtensionAttributes', 'getSku'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);
        $product->expects(static::exactly(2))
            ->method('getSku')
            ->willReturn($sku);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->addMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $extensionAttributes->expects(static::atLeastOnce())
            ->method('getConfigurableProductOptions')
            ->willReturn([]);
        $extensionAttributes->expects(static::atLeastOnce())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);

        $this->optionRepository->expects(static::once())
            ->method('getList')
            ->with($sku)
            ->willReturn([]);
        $this->optionRepository->expects(static::never())
            ->method('deleteById');

        $entity = $this->saveHandler->execute($product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecute()
    {
        $sku = 'config-1';
        $idOld = 25;
        $idNew = 26;
        $attributeIdOld = 11;
        $attributeIdNew = 22;
        $configurableProductLinks = [1, 2, 3];

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeId', 'getSku', 'getData', 'getExtensionAttributes'])
            ->getMock();
        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);
        $product->expects(static::exactly(5))
            ->method('getSku')
            ->willReturn($sku);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->addMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->productRepository->expects($this->once())
            ->method('get')->with($sku, false, null, true);

        $attributeNew = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeId', 'loadByProductAndAttribute', 'setId', 'getId'])
            ->getMock();
        $attributeNew->expects(static::atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeIdNew);
        $this->processSaveOptions($attributeNew, $sku, $idNew);

        $optionOld = $this->getMockForAbstractClass(OptionInterface::class);
        $optionOld->expects(static::atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeIdOld);
        $optionOld->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn($idOld);

        $list = [$optionOld];
        $this->optionRepository->expects(static::atLeastOnce())
            ->method('getList')
            ->with($sku)
            ->willReturn($list);
        $this->optionRepository->expects(static::once())
            ->method('deleteById')
            ->with($sku, $idOld);

        $configurableAttributes = [
            $attributeNew
        ];
        $extensionAttributes->expects(static::atLeastOnce())
            ->method('getConfigurableProductOptions')
            ->willReturn($configurableAttributes);

        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);

        $this->configurable->expects(static::once())
            ->method('saveProducts')
            ->with($product, $configurableProductLinks);

        $entity = $this->saveHandler->execute($product);
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
            ->getMock();

        $this->configurableFactory = $this->getMockBuilder(ConfigurableFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->configurableFactory->expects(static::any())
            ->method('create')
            ->willReturn($this->configurable);
    }

    /**
     * Mock for options save
     *
     * @param MockObject $attribute
     * @param $sku
     * @param $id
     * @return void
     */
    private function processSaveOptions(MockObject $attribute, $sku, $id)
    {
        $attribute->expects(static::once())
            ->method('setId')
            ->with(null)
            ->willReturnSelf();

        $this->optionRepository->expects(static::once())
            ->method('save')
            ->with($sku, $attribute)
            ->willReturn($id);
    }
}

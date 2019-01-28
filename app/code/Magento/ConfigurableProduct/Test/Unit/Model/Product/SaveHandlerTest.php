<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SaveHandlerTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
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

        $this->saveHandler = new SaveHandler(
            $this->configurable,
            $this->optionRepository
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

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects($this->never())
            ->method('getExtensionAttributes');

        $entity = $this->saveHandler->execute($product);
        $this->assertSame($product, $entity);
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
            ->setMethods(['getTypeId', 'getExtensionAttributes', 'getSku'])
            ->getMock();

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);
        $product->expects($this->exactly(1))
            ->method('getSku')
            ->willReturn($sku);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getConfigurableProductOptions')
            ->willReturn([]);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);

        $this->optionRepository->expects($this->once())
            ->method('getList')
            ->with($sku)
            ->willReturn([]);
        $this->optionRepository->expects($this->never())
            ->method('deleteById');

        $entity = $this->saveHandler->execute($product);
        $this->assertSame($product, $entity);
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
            ->setMethods(['getTypeId', 'getSku', 'getData', 'getExtensionAttributes'])
            ->getMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);
        $product->expects($this->exactly(4))
            ->method('getSku')
            ->willReturn($sku);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $attributeNew = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeId', 'loadByProductAndAttribute', 'setId', 'getId'])
            ->getMock();
        $attributeNew->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeIdNew);
        $this->processSaveOptions($attributeNew, $sku, $idNew);

        $optionOld = $this->getMockForAbstractClass(OptionInterface::class);
        $optionOld->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeIdOld);
        $optionOld->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($idOld);

        $list = [$optionOld];
        $this->optionRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->with($sku)
            ->willReturn($list);
        $this->optionRepository->expects($this->once())
            ->method('deleteById')
            ->with($sku, $idOld);

        $configurableAttributes = [
            $attributeNew
        ];
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getConfigurableProductOptions')
            ->willReturn($configurableAttributes);

        $extensionAttributes->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);

        $this->configurable->expects($this->once())
            ->method('saveProducts')
            ->with($product, $configurableProductLinks);

        $entity = $this->saveHandler->execute($product);
        $this->assertSame($product, $entity);
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

        $this->configurableFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->configurable);
    }

    /**
     * Mock for options save
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $attribute
     * @param $sku
     * @param $id
     * @return void
     */
    private function processSaveOptions(\PHPUnit_Framework_MockObject_MockObject $attribute, $sku, $id)
    {
        $attribute->expects($this->once())
            ->method('setId')
            ->with(null)
            ->willReturnSelf();

        $this->optionRepository->expects($this->once())
            ->method('save')
            ->with($sku, $attribute)
            ->willReturn($id);
    }
}

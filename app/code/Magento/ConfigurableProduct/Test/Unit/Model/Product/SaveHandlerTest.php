<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\OptionRepository;
use Magento\ConfigurableProduct\Model\Product\SaveHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Framework\Model\Entity\MetadataPool;
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
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadata|MockObject
     */
    private $metadata;

    /**
     * @var ConfigurableFactory|MockObject
     */
    private $configurableFactory;

    /**
     * @var Configurable|MockObject
     */
    private $configurable;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var Collection|MockObject
     */
    private $attributesCollection;

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
            ->setMethods(['save'])
            ->getMock();

        $this->initMetadataPoolMock();

        $this->initConfigurableFactoryMock();

        $this->initCollectionFactoryMock();

        $this->productRepository = $this->getMock(ProductAttributeRepositoryInterface::class);

        $this->saveHandler = new SaveHandler(
            $this->optionRepository,
            $this->metadataPool,
            $this->configurableFactory,
            $this->collectionFactory,
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
        $this->metadataPool->expects(static::never())
            ->method('getMetadata');
        $this->collectionFactory->expects(static::never())
            ->method('create');

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
        $entityId = 1;
        $linkField = 'entity_id';

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

        $product->expects(static::once())
            ->method('getSku')
            ->willReturn($sku);
        $this->optionRepository->expects(static::once())
            ->method('save')
            ->with($sku, $attribute)
            ->willReturn($id);

        $this->attributesCollection->expects(static::once())
            ->method('setProductFilter')
            ->with($product)
            ->willReturnSelf();

        $this->metadata->expects(static::once())
            ->method('getLinkField')
            ->willReturn($linkField);
        $product->expects(static::once())
            ->method('getData')
            ->with($linkField)
            ->willReturn($entityId);

        $this->attributesCollection->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->attributesCollection->expects(static::once())
            ->method('walk')
            ->with('delete');

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
     * Init mock object for metadata pool
     *
     * @return void
     */
    private function initMetadataPoolMock()
    {
        $this->metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMock();

        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata'])
            ->getMock();

        $this->metadataPool->expects(static::any())
            ->method('getMetadata')
            ->willReturn($this->metadata);
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

    /**
     * Init mock object for collection factory
     *
     * @return void
     */
    private function initCollectionFactoryMock()
    {
        $this->attributesCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setProductFilter', 'addFieldToFilter', 'walk', '__wakeup'])
            ->getMock();

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->collectionFactory->expects(static::any())
            ->method('create')
            ->willReturn($this->attributesCollection);
    }
}

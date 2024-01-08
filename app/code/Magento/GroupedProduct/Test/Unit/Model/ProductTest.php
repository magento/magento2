<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductLinkExtension;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\Cache;
use Magento\Catalog\Model\Product\Image\CacheFactory;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Simple as SimpleProductType;
use Magento\Catalog\Model\ProductLink\CollectionProvider;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 */
class ProductTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Product
     */
    private $model;

    /**
     * @var Manager|MockObject
     */
    private $moduleManager;

    /**
     * @var MockObject
     */
    private $stockItemFactoryMock;

    /**
     * @var IndexerInterface|MockObject
     */
    private $categoryIndexerMock;

    /**
     * @var Processor|MockObject
     */
    private $productFlatProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|MockObject
     */
    private $productPriceProcessor;

    /**
     * @var Product\Type|MockObject
     */
    private $productTypeInstanceMock;

    /**
     * @var Product\Option|MockObject
     */
    private $optionInstanceMock;

    /**
     * @var Base|MockObject
     */
    protected $_priceInfoMock;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var ProductResourceModel|MockObject
     */
    private $resource;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var Website|MockObject
     */
    private $website;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Helper\Product|MockObject
     */
    private $_catalogProduct;

    /**
     * @var Cache|MockObject
     */
    private $imageCache;

    /**
     * @var CacheFactory|MockObject
     */
    private $imageCacheFactory;

    /**
     * @var MockObject
     */
    private $mediaGalleryEntryFactoryMock;

    /**
     * @var MockObject
     */
    private $productLinkFactory;

    /**
     * @var MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var MockObject
     */
    private $metadataServiceMock;

    /**
     * @var MockObject
     */
    private $attributeValueFactory;

    /**
     * @var MockObject
     */
    private $linkTypeProviderMock;

    /**
     * @var MockObject
     */
    private $entityCollectionProviderMock;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->categoryIndexerMock = $this->getMockForAbstractClass(IndexerInterface::class);

        $this->moduleManager = $this->createPartialMock(Manager::class, ['isEnabled']);
        $this->stockItemFactoryMock = $this->createPartialMock(
            StockItemInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFlatProcessor = $this->createMock(Processor::class);

        $this->_priceInfoMock = $this->createMock(Base::class);
        $this->productTypeInstanceMock = $this->createMock(Type::class);
        $this->productPriceProcessor = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Price\Processor::class);

        $stateMock = $this->createPartialMock(State::class, ['getAreaCode']);
        $stateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(FrontNameResolver::AREA_CODE);

        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $actionValidatorMock = $this->createMock(RemoveAction::class);
        $actionValidatorMock->expects($this->any())->method('isAllowed')->willReturn(true);
        $cacheInterfaceMock = $this->getMockForAbstractClass(CacheInterface::class);

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator']
        );
        $contextMock->expects($this->any())->method('getAppState')->willReturn($stateMock);
        $contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $contextMock->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($cacheInterfaceMock);
        $contextMock->expects($this->any())
            ->method('getActionValidator')
            ->willReturn($actionValidatorMock);

        $this->optionInstanceMock = $this->getMockBuilder(Option::class)
            ->onlyMethods(['setProduct', '__wakeup', '__sleep'])
            ->addMethods(['saveOptions'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource = $this->getMockBuilder(ProductResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $storeManager->expects($this->any())
            ->method('getWebsite')
            ->willReturn($this->website);
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);

        $this->_catalogProduct = $this->createPartialMock(
            \Magento\Catalog\Helper\Product::class,
            ['isDataForProductCategoryIndexerWasChanged']
        );

        $this->imageCache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageCacheFactory = $this->getMockBuilder(CacheFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->productLinkFactory = $this->getMockBuilder(ProductLinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->mediaGalleryEntryFactoryMock =
            $this->getMockBuilder(ProductAttributeMediaGalleryEntryInterfaceFactory::class)
                ->onlyMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->metadataServiceMock = $this->getMockForAbstractClass(ProductAttributeRepositoryInterface::class);
        $this->attributeValueFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkTypeProviderMock = $this->createPartialMock(
            LinkTypeProvider::class,
            ['getLinkTypes']
        );
        $this->entityCollectionProviderMock = $this->createPartialMock(
            CollectionProvider::class,
            ['getCollection']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Product::class,
            [
                'context' => $contextMock,
                'catalogProductType' => $this->productTypeInstanceMock,
                'productFlatIndexerProcessor' => $this->productFlatProcessor,
                'productPriceIndexerProcessor' => $this->productPriceProcessor,
                'catalogProductOption' => $this->optionInstanceMock,
                'storeManager' => $storeManager,
                'resource' => $this->resource,
                'registry' => $this->registry,
                'moduleManager' => $this->moduleManager,
                'stockItemFactory' => $this->stockItemFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'indexerRegistry' => $this->indexerRegistryMock,
                'categoryRepository' => $this->categoryRepository,
                'catalogProduct' => $this->_catalogProduct,
                'imageCacheFactory' => $this->imageCacheFactory,
                'productLinkFactory' => $this->productLinkFactory,
                'mediaGalleryEntryFactory' => $this->mediaGalleryEntryFactoryMock,
                'metadataService' => $this->metadataServiceMock,
                'customAttributeFactory' => $this->attributeValueFactory,
                'entityCollectionProvider' => $this->entityCollectionProviderMock,
                'linkTypeProvider' => $this->linkTypeProviderMock,
                'data' => ['id' => 1]
            ]
        );
    }

    /**
     *  Test for getProductLinks() with associated product links.
     *
     * @return void
     */
    public function testGetProductLinks(): void
    {
        $this->markTestSkipped('Skipped due to https://jira.corp.x.com/browse/MAGETWO-36926');
        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())->method('getLinkTypes')->willReturn($linkTypes);

        $inputRelatedLink = $this->objectManagerHelper->getObject(Link::class);
        $inputRelatedLink->setProductSku("Simple Product 1");
        $inputRelatedLink->setLinkType("related");
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type", "simple");
        $inputRelatedLink->setPosition(0);

        $customData = ["attribute_code" => "qty", "value" => 1];
        $inputGroupLink = $this->objectManagerHelper->getObject(Link::class);
        $inputGroupLink->setProductSku("Simple Product 1");
        $inputGroupLink->setLinkType("associated");
        $inputGroupLink->setData("sku", "Simple Product 2");
        $inputGroupLink->setData("type", "simple");
        $inputGroupLink->setPosition(0);
        $inputGroupLink["custom_attributes"] = [$customData];

        $outputRelatedLink = $this->objectManagerHelper->getObject(Link::class);
        $outputRelatedLink->setProductSku("Simple Product 1");
        $outputRelatedLink->setLinkType("related");
        $outputRelatedLink->setLinkedProductSku("Simple Product 2");
        $outputRelatedLink->setLinkedProductType("simple");
        $outputRelatedLink->setPosition(0);

        $groupExtension = $this->objectManagerHelper->getObject(ProductLinkExtension::class);
        $reflectionOfExtension = new \ReflectionClass(ProductLinkExtension::class);
        $method = $reflectionOfExtension->getMethod('setData');
        $method->setAccessible(true);
        $method->invokeArgs($groupExtension, ['qty', 1]);

        $outputGroupLink = $this->objectManagerHelper->getObject(Link::class);
        $outputGroupLink->setProductSku("Simple Product 1");
        $outputGroupLink->setLinkType("associated");
        $outputGroupLink->setLinkedProductSku("Simple Product 2");
        $outputGroupLink->setLinkedProductType("simple");
        $outputGroupLink->setPosition(0);
        $outputGroupLink->setExtensionAttributes($groupExtension);

        $this->entityCollectionProviderMock->method('getCollection')
            ->withConsecutive(
                [$this->model, 'related'],
                [$this->model, 'upsell'],
                [$this->model, 'crosssell'],
                [$this->model, 'associated']
            )
            ->willReturnOnConsecutiveCalls([$inputRelatedLink], [], [], [$inputGroupLink]);

        $expectedOutput = [$outputRelatedLink, $outputGroupLink];
        $typeInstanceMock = $this->getMockBuilder(SimpleProductType::class)
            ->addMethods(['"getSku"'])
            ->getMock();
        $typeInstanceMock->expects($this->atLeastOnce())->method('getSku')->willReturn("Simple Product 1");
        $this->model->setTypeInstance($typeInstanceMock);

        $productLink1 = $this->objectManagerHelper->getObject(Link::class);
        $productLink2 = $this->objectManagerHelper->getObject(Link::class);
        $this->productLinkFactory->method('create')
            ->willReturnOnConsecutiveCalls($productLink1, $productLink2);

        $extension = $this->objectManagerHelper->getObject(ProductLinkExtension::class);
        $productLink2->setExtensionAttributes($extension);

        $links = $this->model->getProductLinks();
        // Match the links
        $matches = 0;
        foreach ($links as $link) {
            foreach ($expectedOutput as $expected) {
                if ($expected->getData() == $link->getData()) {
                    $matches++;
                }
            }
        }
        $this->assertEquals($matches, 2);
    }
}

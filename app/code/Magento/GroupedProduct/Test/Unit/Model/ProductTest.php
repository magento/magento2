<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Unit\Model;

use \Magento\Catalog\Model\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Product Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemFactoryMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryIndexerMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFlatProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productPriceProcessor;

    /**
     * @var Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeInstanceMock;

    /**
     * @var Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionInstanceMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceInfoMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    private $website;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_catalogProduct;

    /**
     * @var \Magento\Catalog\Model\Product\Image\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageCache;

    /**
     * @var \Magento\Catalog\Model\Product\Image\CacheFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageCacheFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryEntryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeValueFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityCollectionProviderMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->categoryIndexerMock = $this->getMockForAbstractClass(\Magento\Framework\Indexer\IndexerInterface::class);

        $this->moduleManager = $this->createPartialMock(\Magento\Framework\Module\Manager::class, ['isEnabled']);
        $this->stockItemFactoryMock = $this->createPartialMock(
            \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFlatProcessor = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Processor::class);

        $this->_priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->productTypeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type::class);
        $this->productPriceProcessor = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Price\Processor::class);

        $stateMock = $this->createPartialMock(\Magento\Framework\App\State::class, ['getAreaCode']);
        $stateMock->expects($this->any())
            ->method('getAreaCode')
            ->will($this->returnValue(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE));

        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $actionValidatorMock = $this->createMock(\Magento\Framework\Model\ActionValidator\RemoveAction::class);
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $cacheInterfaceMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);

        $contextMock = $this->createPartialMock(
            \Magento\Framework\Model\Context::class,
            ['getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator']
        );
        $contextMock->expects($this->any())->method('getAppState')->will($this->returnValue($stateMock));
        $contextMock->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventManagerMock));
        $contextMock->expects($this->any())
            ->method('getCacheManager')
            ->will($this->returnValue($cacheInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getActionValidator')
            ->will($this->returnValue($actionValidatorMock));

        $this->optionInstanceMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['setProduct', 'saveOptions', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()->getMock();

        $this->resource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));
        $storeManager->expects($this->any())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

        $this->_catalogProduct = $this->createPartialMock(
            \Magento\Catalog\Helper\Product::class,
            ['isDataForProductCategoryIndexerWasChanged']
        );

        $this->imageCache = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image\Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageCacheFactory = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image\CacheFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productLinkFactory = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mediaGalleryEntryFactoryMock =
            $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->metadataServiceMock = $this->createMock(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
        $this->attributeValueFactory = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->linkTypeProviderMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\LinkTypeProvider::class,
            ['getLinkTypes']
        );
        $this->entityCollectionProviderMock = $this->createPartialMock(
            \Magento\Catalog\Model\ProductLink\CollectionProvider::class,
            ['getCollection']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
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
     *  Test for getProductLinks() with associated product links
     */
    public function testGetProductLinks()
    {
        $this->markTestIncomplete('Skipped due to https://jira.corp.x.com/browse/MAGETWO-36926');
        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())->method('getLinkTypes')->willReturn($linkTypes);

        $inputRelatedLink = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku("Simple Product 1");
        $inputRelatedLink->setLinkType("related");
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type", "simple");
        $inputRelatedLink->setPosition(0);

        $customData = ["attribute_code" => "qty", "value" => 1];
        $inputGroupLink = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputGroupLink->setProductSku("Simple Product 1");
        $inputGroupLink->setLinkType("associated");
        $inputGroupLink->setData("sku", "Simple Product 2");
        $inputGroupLink->setData("type", "simple");
        $inputGroupLink->setPosition(0);
        $inputGroupLink["custom_attributes"] = [$customData];

        $outputRelatedLink = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $outputRelatedLink->setProductSku("Simple Product 1");
        $outputRelatedLink->setLinkType("related");
        $outputRelatedLink->setLinkedProductSku("Simple Product 2");
        $outputRelatedLink->setLinkedProductType("simple");
        $outputRelatedLink->setPosition(0);

        $groupExtension = $this->objectManagerHelper->getObject(\Magento\Catalog\Api\Data\ProductLinkExtension::class);
        $reflectionOfExtension = new \ReflectionClass(\Magento\Catalog\Api\Data\ProductLinkExtension::class);
        $method = $reflectionOfExtension->getMethod('setData');
        $method->setAccessible(true);
        $method->invokeArgs($groupExtension, ['qty', 1]);

        $outputGroupLink = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $outputGroupLink->setProductSku("Simple Product 1");
        $outputGroupLink->setLinkType("associated");
        $outputGroupLink->setLinkedProductSku("Simple Product 2");
        $outputGroupLink->setLinkedProductType("simple");
        $outputGroupLink->setPosition(0);
        $outputGroupLink->setExtensionAttributes($groupExtension);

        $this->entityCollectionProviderMock->expects($this->at(0))
            ->method('getCollection')
            ->with($this->model, 'related')
            ->willReturn([$inputRelatedLink]);
        $this->entityCollectionProviderMock->expects($this->at(1))
            ->method('getCollection')
            ->with($this->model, 'upsell')
            ->willReturn([]);
        $this->entityCollectionProviderMock->expects($this->at(2))
            ->method('getCollection')
            ->with($this->model, 'crosssell')
            ->willReturn([]);
        $this->entityCollectionProviderMock->expects($this->at(3))
            ->method('getCollection')
            ->with($this->model, 'associated')
            ->willReturn([$inputGroupLink]);

        $expectedOutput = [$outputRelatedLink, $outputGroupLink];
        $typeInstanceMock = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Simple::class)
            ->setMethods(["getSku"])
            ->getMock();
        $typeInstanceMock->expects($this->atLeastOnce())->method('getSku')->willReturn("Simple Product 1");
        $this->model->setTypeInstance($typeInstanceMock);

        $productLink1 = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $productLink2 = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $this->productLinkFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($productLink1);
        $this->productLinkFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($productLink2);

        $extension = $this->objectManagerHelper->getObject(\Magento\Catalog\Api\Data\ProductLinkExtension::class);
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

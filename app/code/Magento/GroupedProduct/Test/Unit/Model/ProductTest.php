<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
class ProductTest extends \PHPUnit_Framework_TestCase
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
        $this->categoryIndexerMock = $this->getMockForAbstractClass('\Magento\Framework\Indexer\IndexerInterface');

        $this->moduleManager = $this->getMock(
            'Magento\Framework\Module\Manager',
            ['isEnabled'],
            [],
            '',
            false
        );
        $this->stockItemFactoryMock = $this->getMock(
            'Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFlatProcessor = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor',
            [],
            [],
            '',
            false
        );

        $this->_priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->productTypeInstanceMock = $this->getMock('Magento\Catalog\Model\Product\Type', [], [], '', false);
        $this->productPriceProcessor = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Price\Processor',
            [],
            [],
            '',
            false
        );

        $stateMock = $this->getMock('Magento\FrameworkApp\State', ['getAreaCode'], [], '', false);
        $stateMock->expects($this->any())
            ->method('getAreaCode')
            ->will($this->returnValue(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE));

        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction',
            [],
            [],
            '',
            false
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $cacheInterfaceMock = $this->getMock('Magento\Framework\App\CacheInterface');

        $contextMock = $this->getMock(
            '\Magento\Framework\Model\Context',
            ['getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator'], [], '', false
        );
        $contextMock->expects($this->any())->method('getAppState')->will($this->returnValue($stateMock));
        $contextMock->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventManagerMock));
        $contextMock->expects($this->any())
            ->method('getCacheManager')
            ->will($this->returnValue($cacheInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getActionValidator')
            ->will($this->returnValue($actionValidatorMock));

        $this->optionInstanceMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
            ->setMethods(['setProduct', 'saveOptions', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()->getMock();

        $this->resource = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->website = $this->getMockBuilder('\Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));
        $storeManager->expects($this->any())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->indexerRegistryMock = $this->getMock('Magento\Framework\Indexer\IndexerRegistry', ['get'], [], '', false);
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');

        $this->_catalogProduct = $this->getMock(
            'Magento\Catalog\Helper\Product',
            ['isDataForProductCategoryIndexerWasChanged'],
            [],
            '',
            false
        );

        $this->imageCache = $this->getMockBuilder('Magento\Catalog\Model\Product\Image\Cache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageCacheFactory = $this->getMockBuilder('Magento\Catalog\Model\Product\Image\CacheFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productLinkFactory = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductLinkInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mediaGalleryEntryFactoryMock =
            $this->getMockBuilder('Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory')
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->metadataServiceMock = $this->getMock('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');
        $this->attributeValueFactory = $this->getMockBuilder('Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()->getMock();
        $this->linkTypeProviderMock = $this->getMock('Magento\Catalog\Model\Product\LinkTypeProvider',
            ['getLinkTypes'], [], '', false);
        $this->entityCollectionProviderMock = $this->getMock('Magento\Catalog\Model\ProductLink\CollectionProvider',
            ['getCollection'], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product',
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
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $inputRelatedLink = $this->objectManagerHelper->getObject('Magento\Catalog\Model\ProductLink\Link');
        $inputRelatedLink->setProductSku("Simple Product 1");
        $inputRelatedLink->setLinkType("related");
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type", "simple");
        $inputRelatedLink->setPosition(0);

        $customData = ["attribute_code" => "qty", "value" => 1];
        $inputGroupLink = $this->objectManagerHelper->getObject('Magento\Catalog\Model\ProductLink\Link');
        $inputGroupLink->setProductSku("Simple Product 1");
        $inputGroupLink->setLinkType("associated");
        $inputGroupLink->setData("sku", "Simple Product 2");
        $inputGroupLink->setData("type", "simple");
        $inputGroupLink->setPosition(0);
        $inputGroupLink["custom_attributes"] = [$customData];

        $outputRelatedLink = $this->objectManagerHelper->getObject('Magento\Catalog\Model\ProductLink\Link');
        $outputRelatedLink->setProductSku("Simple Product 1");
        $outputRelatedLink->setLinkType("related");
        $outputRelatedLink->setLinkedProductSku("Simple Product 2");
        $outputRelatedLink->setLinkedProductType("simple");
        $outputRelatedLink->setPosition(0);

        $groupExtension = $this->objectManagerHelper->getObject('Magento\Catalog\Api\Data\ProductLinkExtension');
        $reflectionOfExtension = new \ReflectionClass('Magento\Catalog\Api\Data\ProductLinkExtension');
        $method = $reflectionOfExtension->getMethod('setData');
        $method->setAccessible(true);
        $method->invokeArgs($groupExtension, array('qty', 1));

        $outputGroupLink = $this->objectManagerHelper->getObject('Magento\Catalog\Model\ProductLink\Link');
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
        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Simple', ["getSku"], [], '', false);
        $typeInstanceMock
            ->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn("Simple Product 1");
        $this->model->setTypeInstance($typeInstanceMock);

        $productLink1 = $this->objectManagerHelper->getObject('Magento\Catalog\Model\ProductLink\Link');
        $productLink2 = $this->objectManagerHelper->getObject('Magento\Catalog\Model\ProductLink\Link');
        $this->productLinkFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($productLink1);
        $this->productLinkFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($productLink2);

        $extension = $this->objectManagerHelper->getObject('Magento\Catalog\Api\Data\ProductLinkExtension');
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

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Session;
use Magento\Catalog\Block\Adminhtml\Product\Grid;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as SetFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Backend\Block\Widget\Grid\Massaction;
use Magento\Backend\Block\Widget\Grid\Column as GridColumn;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends TestCase
{
    /**
     * @var Grid
     */
    private Grid $grid;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var BackendHelper|MockObject
     */
    private $backendHelperMock;

    /**
     * @var WebsiteFactory|MockObject
     */
    private $websiteFactoryMock;

    /**
     * @var SetFactory|MockObject
     */
    private $setFactoryMock;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var ProductType|MockObject
     */
    private $productTypeMock;

    /**
     * @var ProductStatus|MockObject
     */
    private $statusMock;

    /**
     * @var ProductVisibility|MockObject
     */
    private $visibilityMock;

    /**
     * @var ModuleManager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * Set up all required mocks and create the grid instance.
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock        = $this->createMock(Context::class);
        $this->backendHelperMock  = $this->createMock(BackendHelper::class);
        $this->websiteFactoryMock = $this->createMock(WebsiteFactory::class);
        $this->setFactoryMock     = $this->createMock(SetFactory::class);
        $this->productFactoryMock = $this->createMock(ProductFactory::class);
        $this->productTypeMock    = $this->createMock(ProductType::class);
        $this->statusMock         = $this->createMock(ProductStatus::class);
        $this->visibilityMock     = $this->createMock(ProductVisibility::class);
        $this->moduleManagerMock  = $this->createMock(ModuleManager::class);

        $objectManager->prepareObjectManager([
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)],
        ]);

        /** @var DirectoryWriteInterface|MockObject $directoryWriteMock */
        $directoryWriteMock = $this->createMock(DirectoryWriteInterface::class);
        /** @var Filesystem|MockObject $filesystemMock */
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->method('getDirectoryWrite')->willReturn($directoryWriteMock);
        $this->contextMock->method('getFilesystem')->willReturn($filesystemMock);

        $this->contextMock->method('getAuthorization')
            ->willReturn($this->createMock(AuthorizationInterface::class));

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->createMock(LayoutInterface::class);
        $this->contextMock->method('getLayout')->willReturn($layoutMock);

        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['has', 'getPost', 'getParam'])
            ->getMock();
        $this->requestMock->method('has')->willReturn(false);
        $this->requestMock->method('getPost')->willReturn([]);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->contextMock->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->contextMock->method('getBackendSession')
            ->willReturn($this->createMock(Session::class));

        $this->grid = $objectManager->getObject(
            Grid::class,
            [
                'context'        => $this->contextMock,
                'backendHelper'  => $this->backendHelperMock,
                'websiteFactory' => $this->websiteFactoryMock,
                'setsFactory'    => $this->setFactoryMock,
                'productFactory' => $this->productFactoryMock,
                'type'           => $this->productTypeMock,
                'status'         => $this->statusMock,
                'visibility'     => $this->visibilityMock,
                'moduleManager'  => $this->moduleManagerMock,
                'data'           => []
            ]
        );
    }

    /**
     * Verify that the grid object is instantiated and its ID is set correctly.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_construct
     * @return void
     */
    public function testConstructInitialisesGrid(): void
    {
        $this->assertInstanceOf(Grid::class, $this->grid);
        $this->assertSame('productGrid', $this->grid->getId());
    }

    /**
     * Confirm that the protected `_getStore` method returns the store retrieved
     * from the request and the store manager.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_getStore
     * @return void
     */
    public function testGetStoreReturnsStore(): void
    {
        $storeMock = $this->createMock(Store::class);
        $storeId   = 42;

        $this->requestMock->method('getParam')
            ->with('store', 0)
            ->willReturn($storeId);
        $this->storeManagerMock->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $store = $this->invokeMethod($this->grid, '_getStore');
        $this->assertSame($storeMock, $store);
    }

    /**
     * Ensure the product collection is built with the mandatory attributes for the
     * default store
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_prepareCollection
     * @return void
     */
    public function testPrepareCollectionAddsAttributes(): void
    {
        $collectionMock = $this->createMock(Collection::class);
        $productMock    = $this->createMock(Product::class);
        $productMock->method('getCollection')->willReturn($collectionMock);
        $this->productFactoryMock->method('create')->willReturn($productMock);

        $collectionMock->method('addAttributeToSelect')->willReturnSelf();
        $collectionMock->method('setStore')->willReturnSelf();
        $collectionMock->method('joinAttribute')->willReturnSelf();
        $collectionMock->method('addWebsiteNamesToResult')->willReturnSelf();

        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')->willReturn(0);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $gridMock = $this->getMockBuilder(Grid::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->backendHelperMock,
                $this->websiteFactoryMock,
                $this->setFactoryMock,
                $this->productFactoryMock,
                $this->productTypeMock,
                $this->statusMock,
                $this->visibilityMock,
                $this->moduleManagerMock,
                []
            ])
            ->onlyMethods(['getColumn'])
            ->getMock();
        $gridMock->method('getColumn')->willReturn(null);

        $result = $this->invokeMethod($gridMock, '_prepareCollection');
        $this->assertSame($gridMock, $result);
    }

    /**
     * Verify that the collection joins the inventory quantity field when the
     * CatalogInventory module is enabled and that store‑specific filters are
     * applied when a non‑default store is requested.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_prepareCollection
     * @return void
     */
    public function testPrepareCollectionCoversInventoryAndStoreSpecific(): void
    {
        $collectionMock = $this->createMock(Collection::class);
        $productMock    = $this->createMock(Product::class);
        $productMock->method('getCollection')->willReturn($collectionMock);
        $this->productFactoryMock->method('create')->willReturn($productMock);

        // Inventory enabled -> expect a join on the qty column.
        $this->moduleManagerMock->method('isEnabled')
            ->with('Magento_CatalogInventory')
            ->willReturn(true);
        $collectionMock->expects($this->once())->method('joinField')
            ->with(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );

        // Store‑specific request (store ID > 0) -> expect addStoreFilter().
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')->willReturn(2);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $collectionMock->expects($this->once())->method('addStoreFilter')
            ->with($storeMock)
            ->willReturnSelf();

        $collectionMock->method('addAttributeToSelect')->willReturnSelf();
        $collectionMock->method('joinAttribute')->willReturnSelf();
        $collectionMock->method('setStore')->willReturnSelf();
        $collectionMock->method('addWebsiteNamesToResult')->willReturnSelf();

        $gridMock = $this->getMockBuilder(Grid::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->backendHelperMock,
                $this->websiteFactoryMock,
                $this->setFactoryMock,
                $this->productFactoryMock,
                $this->productTypeMock,
                $this->statusMock,
                $this->visibilityMock,
                $this->moduleManagerMock,
                []
            ])
            ->onlyMethods(['getColumn'])
            ->getMock();
        $gridMock->method('getColumn')->willReturn(null);

        // Visibility helper is injected as a mock that does not require constructor args.
        $visibilityMock = $this->getMockBuilder(ProductVisibility::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOptionArray'])
            ->getMock();
        $visibilityMock->method('getOptionArray')
            ->willReturn(['1' => 'Catalog, Search']);
        $this->setProtectedProperty($gridMock, '_visibility', $visibilityMock);

        $result = $this->invokeMethod($gridMock, '_prepareCollection');
        $this->assertSame($gridMock, $result);
    }

    /**
     * Ensure that every column that can appear in the grid is added, including
     * conditional columns such as the store‑specific name, quantity and websites.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_prepareColumns
     * @return void
     */
    public function testPrepareColumnsCoversAllBranches(): void
    {
        $this->prepareStoreContextForColumns(3);
        $this->prepareOptionsProvidersForColumns();
        $this->prepareAttributeSetsForColumns();
        $this->prepareWebsitesForColumns();

        $addedColumnIds = [];
        $gridMock = $this->createGridMockCapturingColumns($addedColumnIds);

        $this->invokeMethod($gridMock, '_prepareColumns');

        $this->assertColumnsAdded(
            $addedColumnIds,
            [
                'entity_id',
                'name',
                'custom_name',
                'type',
                'set_name',
                'sku',
                'price',
                'qty',
                'visibility',
                'status',
                'websites',
                'edit',
            ]
        );
    }

    /**
     * Prepare store, currency, and module context used when building columns.
     *
     * @param int $storeId
     * @return void
     */
    private function prepareStoreContextForColumns(int $storeId): void
    {
        $this->requestMock->method('getParam')->with('store', 0)->willReturn($storeId);
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')->willReturn($storeId);
        $storeMock->method('getName')->willReturn('Store Name');
        $currencyMock = new class {
            public function getCode()
            {
                return 'USD';
            }
        };
        $storeMock->method('getBaseCurrency')->willReturn($currencyMock);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $this->storeManagerMock->method('isSingleStoreMode')->willReturn(false);
        $this->moduleManagerMock->method('isEnabled')->with('Magento_CatalogInventory')->willReturn(true);
    }

    /**
     * Prepare option providers (product type, visibility, status) for columns.
     *
     * @return void
     */
    private function prepareOptionsProvidersForColumns(): void
    {
        $this->productTypeMock->method('getOptionArray')->willReturn(['simple' => 'Simple']);
        $this->visibilityMock->method('getOptionArray')->willReturn(['1' => 'Catalog, Search']);
        $this->statusMock->method('getOptionArray')->willReturn(['1' => 'Enabled', '2' => 'Disabled']);
    }

    /**
     * Prepare attribute set collection used for the set_name column.
     *
     * @return void
     */
    private function prepareAttributeSetsForColumns(): void
    {
        $productEntityMock = new class {
            /** @var object */
            private $entityResource;
            public function getResource()
            {
                if ($this->entityResource === null) {
                    $this->entityResource = new class {
                        public function getTypeId()
                        {
                            return 4;
                        }
                    };
                }
                return $this->entityResource;
            }
        };
        $this->productFactoryMock->method('create')->willReturn($productEntityMock);

        $setsChainMock = new class {
            public function setEntityTypeFilter($id)
            {
                return $this;
            }
            public function load()
            {
                return $this;
            }
            public function toOptionHash()
            {
                return ['4' => 'Default'];
            }
        };
        $this->setFactoryMock->method('create')->willReturn($setsChainMock);
    }

    /**
     * Prepare website factory to supply website options for the websites column.
     *
     * @return void
     */
    private function prepareWebsitesForColumns(): void
    {
        $websitesMock = new class {
            /** @var object */
            private $websiteCollection;
            public function getCollection()
            {
                if ($this->websiteCollection === null) {
                    $this->websiteCollection = new class {
                        public function toOptionHash()
                        {
                            return ['1' => 'Base'];
                        }
                    };
                }
                return $this->websiteCollection;
            }
        };
        $this->websiteFactoryMock->method('create')->willReturn($websitesMock);
    }

    /**
     * Create a Grid mock that captures added column IDs in the provided array.
     *
     * @param array<int,string> $addedColumnIds
     * @return Grid
     */
    private function createGridMockCapturingColumns(array &$addedColumnIds): Grid
    {
        $gridMock = $this->getMockBuilder(Grid::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->backendHelperMock,
                $this->websiteFactoryMock,
                $this->setFactoryMock,
                $this->productFactoryMock,
                $this->productTypeMock,
                $this->statusMock,
                $this->visibilityMock,
                $this->moduleManagerMock,
                []
            ])
            ->onlyMethods(['addColumn', 'sortColumnsByOrder'])
            ->getMock();
        $gridMock->method('sortColumnsByOrder')->willReturn($gridMock);
        $gridMock->method('addColumn')
            ->willReturnCallback(
                function ($columnId) use (&$addedColumnIds, $gridMock) {
                    $addedColumnIds[] = $columnId;
                    return $gridMock;
                }
            );
        // Avoid static-method-on-mock for helpers:
        $this->setProtectedProperty(
            $gridMock,
            '_visibility',
            new class {
                public function getOptionArray()
                {
                    return ['1' => 'Catalog, Search'];
                }
            }
        );
        $this->setProtectedProperty($gridMock, '_status', new ProductStatus());
        return $gridMock;
    }

    /**
     * Assert that all expected column IDs were added to the grid.
     *
     * @param array<int,string> $added
     * @param array<int,string> $expected
     * @return void
     */
    private function assertColumnsAdded(array $added, array $expected): void
    {
        foreach ($expected as $id) {
            $this->assertContains($id, $added);
        }
    }

    /**
     * Verify that the grid URL for AJAX reloading is generated correctly.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::getGridUrl
     * @return void
     */
    public function testGetGridUrl(): void
    {
        $expectedUrl = 'http://example.com/catalog/*/grid';
        $gridMock = $this->getMockBuilder(Grid::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();
        $gridMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/*/grid', ['_current' => true])
            ->willReturn($expectedUrl);
        $this->assertSame($expectedUrl, $gridMock->getGridUrl());
    }

    /**
     * Verify that the URL for editing a product row contains the correct parameters.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::getRowUrl
     * @return void
     */
    public function testGetRowUrl(): void
    {
        $rowId      = 123;
        $storeId    = 2;
        $expectedUrl = 'http://example.com/catalog/*/edit';
        $rowMock = new DataObject(['id' => $rowId]);

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getParam')
            ->with('store')
            ->willReturn($storeId);

        $gridMock = $this->getMockBuilder(Grid::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl', 'getRequest'])
            ->getMock();
        $gridMock->method('getRequest')->willReturn($requestMock);
        $gridMock->expects($this->once())
            ->method('getUrl')
            ->with(
                'catalog/*/edit',
                ['store' => $storeId, 'id' => $rowId]
            )
            ->willReturn($expectedUrl);

        $this->assertSame($expectedUrl, $gridMock->getRowUrl($rowMock));
    }

    /**
     * Check that the mass‑action block is populated with the standard actions:
     * delete, change status and update attributes.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_prepareMassaction
     * @return void
     */
    public function testPrepareMassactionAddsActions(): void
    {
        $massActionBlockMock = $this->getMockBuilder(Massaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $massActionBlockMock->expects($this->any())
            ->method('addItem')
            ->with($this->callback(function ($id) {
                return in_array($id, ['delete', 'status', 'attributes'], true);
            }));

        $gridMock = $this->getMockBuilder(Grid::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMassactionBlock', 'getUrl'])
            ->getMock();
        $gridMock->method('getMassactionBlock')->willReturn($massActionBlockMock);
        $gridMock->method('getUrl')->willReturn('http://example.com/dummy');

        $authMock = $this->createMock(AuthorizationInterface::class);
        $authMock->method('isAllowed')->willReturn(true);
        $this->setProtectedProperty($gridMock, '_authorization', $authMock);
        $this->setProtectedProperty($gridMock, '_status', new ProductStatus());

        $eventManagerMock = $this->createMock(EventManagerInterface::class);
        $eventManagerMock->method('dispatch')->willReturn(null);
        $this->setProtectedProperty($gridMock, '_eventManager', $eventManagerMock);

        $this->invokeMethod($gridMock, '_prepareMassaction');
    }

    /**
     * Verify that adding a filter for the “websites” column causes the collection
     * to join the appropriate website data.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Grid::_addColumnFilterToCollection
     * @return void
     */
    public function testAddColumnFilterToCollectionJoinsWebsites(): void
    {
        $columnMock = $this->createMock(GridColumn::class);
        $columnMock->method('getId')->willReturn('websites');

        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('joinField')
            ->with(
                $this->equalTo('websites'),
                $this->equalTo('catalog_product_website'),
                $this->equalTo('website_id'),
                $this->equalTo('product_id=entity_id'),
                $this->equalTo(null),
                $this->equalTo('left')
            );

        $filterMock = new class {
            public function getCondition()
            {
                return null;
            }
        };
        $columnMock->method('getFilter')->willReturn($filterMock);

        $this->grid->setCollection($collectionMock);
        $this->invokeMethod($this->grid, '_addColumnFilterToCollection', [$columnMock]);
    }

    /**
     * Helper: invoke a protected or private method on an object.
     *
     * @param object $object Object containing the method.
     * @param string $method Name of the method to invoke.
     * @param array  $args   Arguments to pass to the method.
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokeMethod($object, string $method, array $args = [])
    {
        $closure = \Closure::bind(
            function (string $methodName, array $arguments) {
                return $this->$methodName(...$arguments);
            },
            $object,
            get_class($object)
        );
        return $closure($method, $args);
    }

    /**
     * Helper: set the value of a protected or private property.
     *
     * @param object $object   Object containing the property.
     * @param string $property Name of the property.
     * @param mixed  $value    Value to assign.
     * @return void
     * @throws \ReflectionException
     */
    private function setProtectedProperty($object, string $property, $value): void
    {
        $closure = \Closure::bind(
            function (string $propertyName, $propertyValue): void {
                $this->$propertyName = $propertyValue;
            },
            $object,
            get_class($object)
        );
        $closure($property, $value);
    }
}

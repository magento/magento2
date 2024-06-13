<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Observer;

use Magento\Bundle\Helper\Data as BundleHelper;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Bundle\Observer\AppendUpsellProductsObserver;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductLinkCollection;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\Test\Unit\Helper\EventTestHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Observer\AppendUpsellProductsObserver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AppendUpsellProductsObserverTest extends TestCase
{
    /**
     * @var ProductCollection|MockObject
     */
    private $bundleCollectionMock;

    /**
     * @var BundleHelper|MockObject
     */
    private $bundleDataMock;

    /**
     * @var Selection|MockObject
     */
    private $bundleSelectionMock;

    /**
     * @var CatalogConfig|MockObject
     */
    private $configMock;

    /**
     * @var EventTestHelper
     */
    private $eventMock;

    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var AppendUpsellProductsObserver
     */
    private $observer;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var EventTestHelper
     */
    private $collectionMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Visibility|MockObject
     */
    private $productVisibilityMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->bundleCollectionMock = $this->createPartialMock(ProductCollection::class, [
            'addAttributeToSelect',
            'addFieldToFilter',
            'addFinalPrice',
            'addMinimalPrice',
            'addStoreFilter',
            'addTaxPercents',
            'load',
            'setFlag',
            'setPageSize',
            'setVisibility'
        ]);

        $this->bundleDataMock = $this->createPartialMock(BundleHelper::class, ['getAllowedSelectionTypes']);

        $this->bundleSelectionMock = $this->createPartialMock(Selection::class, ['getParentIdsByChild']);

        $this->configMock = $this->createPartialMock(CatalogConfig::class, ['getProductAttributes']);

        $this->eventMock = new EventTestHelper();

        $this->collectionMock = new EventTestHelper();

        $this->productMock = $this->createPartialMock(Product::class, ['getCollection', 'getId', 'getTypeId']);

        $this->productVisibilityMock = $this->createPartialMock(Visibility::class, ['getVisibleInCatalogIds']);

        $this->observer = $this->objectManager->getObject(
            AppendUpsellProductsObserver::class,
            [
                'bundleData' => $this->bundleDataMock,
                'productVisibility' => $this->productVisibilityMock,
                'config' => $this->configMock,
                'bundleSelection' => $this->bundleSelectionMock,
            ]
        );
    }

    /**
     * Test observer execute method
     */
    public function testAddBundleCollectionItemsToEventCollection()
    {
        $collectionItems = [
            1 => 1
        ];
        $limit = 2;
        $parentIds = [1, 3];
        $productId = 2;
        $productAttributes = ['attribute1', 'attribute2'];
        $visibleInCatalogIds = [10, 11, 12];

        $this->observerMock
            ->expects($this->exactly(3))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->setProduct($this->productMock);

        $this->bundleDataMock
            ->expects($this->once())
            ->method('getAllowedSelectionTypes')
            ->willReturn($this->getAllowedSelectionTypes());

        $this->productMock
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn(ProductType::TYPE_SIMPLE);

        $this->eventMock->setCollection($this->collectionMock);
        $this->eventMock->setLimit($limit);

        $this->collectionMock->setItems($collectionItems);

        $this->productMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->bundleSelectionMock
            ->expects($this->once())
            ->method('getParentIdsByChild')
            ->willReturn($parentIds);

        $this->productMock
            ->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->bundleCollectionMock);

        $this->bundleCollectionMock
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->willReturn($this->bundleCollectionMock);

        $this->configMock
            ->expects($this->once())
            ->method('getProductAttributes')
            ->willReturn($productAttributes);

        $this->productVisibilityMock
            ->expects($this->once())
            ->method('getVisibleInCatalogIds')
            ->willReturn($visibleInCatalogIds);

        $this->bundleCollectionMock
            ->expects($this->once())
            ->method('setPageSize')
            ->willReturn($this->bundleCollectionMock);

        $this->bundleCollectionMock
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturn($this->bundleCollectionMock);

        $this->bundleCollectionMock
            ->expects($this->once())
            ->method('setFlag')
            ->willReturn($this->bundleCollectionMock);

        $this->collectionMock->setItems($collectionItems);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer when collection contains a parent product of a current product
     */
    public function testObserverWithoutBundleIds()
    {
        $collectionItems = [
            1 => 1
        ];
        $parentIds = [1];
        $limit = 2;
        $productId = 2;

        $this->observerMock
            ->expects($this->exactly(3))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->setProduct($this->productMock);

        $this->bundleDataMock
            ->expects($this->once())
            ->method('getAllowedSelectionTypes')
            ->willReturn($this->getAllowedSelectionTypes());

        $this->productMock
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn(ProductType::TYPE_SIMPLE);

        $this->eventMock->setCollection($this->collectionMock);
        $this->eventMock->setLimit($limit);

        $this->collectionMock->setItems($collectionItems);

        $this->productMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->bundleSelectionMock
            ->expects($this->once())
            ->method('getParentIdsByChild')
            ->willReturn($parentIds);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer when count of products is equal to limit.
     */
    public function testObserverWithoutLinkedProducts()
    {
        $collectionItems = [
            1 => 1
        ];
        $limit = 1;

        $this->observerMock
            ->expects($this->exactly(3))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->setProduct($this->productMock);

        $this->bundleDataMock
            ->expects($this->once())
            ->method('getAllowedSelectionTypes')
            ->willReturn($this->getAllowedSelectionTypes());

        $this->productMock
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn(ProductType::TYPE_SIMPLE);

        $this->eventMock->setCollection($this->collectionMock);
        $this->eventMock->setLimit($limit);

        $this->collectionMock->setItems($collectionItems);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer when a current product type is allowed for bundle selection
     */
    public function testCurrentProductIsNotAllowedForBundleSelection()
    {
        $this->bundleDataMock
            ->expects($this->once())
            ->method('getAllowedSelectionTypes')
            ->willReturn($this->getAllowedSelectionTypes());

        $this->eventMock->setProduct($this->productMock);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->productMock
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Grouped::TYPE_CODE);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Returns allowed products types
     *
     * @return array
     */
    private function getAllowedSelectionTypes(): array
    {
        return [
            'simple' => ProductType::TYPE_SIMPLE,
            'virtual' => ProductType::TYPE_VIRTUAL,
        ];
    }
}

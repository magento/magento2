<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductProcessUrlRewriteSavingObserverTest
 *
 * Tests the ProductProcessUrlRewriteSavingObserver to ensure the
 * replace method (refresh existing URLs) and deleteByData (remove
 * old URLs) are called the correct number of times.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessUrlRewriteSavingObserverTest extends TestCase
{
    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersist;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var Observer|MockObject
     */
    private $observer;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Product|MockObject
     */
    private $product1;

    /**
     * @var Product|MockObject
     */
    private $product2;

    /**
     * @var Product|MockObject
     */
    private $product5;

    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    private $productUrlRewriteGenerator;

    /**
     * @var ProductScopeRewriteGenerator|MockObject
     */
    private $productScopeRewriteGenerator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductProcessUrlRewriteSavingObserver
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Website|MockObject
     */
    private $website1;

    /**
     * @var Website|MockObject
     */
    private $website2;

    /**
     * @var StoreWebsiteRelationInterface|MockObject
     */
    private $storeWebsiteRelation;

    /**
     * @var ProductRepository|MockObject
     */
    private $productRepository;

    /**
     * @var DbStorage|MockObject
     */
    private $dbStorage;

    /**
     * Set up
     * Website_ID = 1 -> Store_ID = 1
     * Website_ID = 2 -> Store_ID = 2 & 5
     */
    protected function setUp()
    {
        $this->urlPersist = $this->createMock(UrlPersistInterface::class);
        $this->product = $this->createPartialMock(
            Product::class,
            [
                'getId',
                'dataHasChangedFor',
                'isVisibleInSiteVisibility',
                'getIsChangedWebsites',
                'getIsChangedCategories',
                'getStoreId',
                'getWebsiteIds'
            ]
        );
        $this->product1 = $this->createPartialMock(
            Product::class,
            ['getId', 'isVisibleInSiteVisibility']
        );
        $this->product2 = $this->createPartialMock(
            Product::class,
            ['getId', 'isVisibleInSiteVisibility']
        );
        $this->product5 = $this->createPartialMock(
            Product::class,
            ['getId', 'isVisibleInSiteVisibility']
        );
        $this->productRepository = $this->createPartialMock(ProductRepository::class, ['getById']);
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->product1->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->product2->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->product5->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValueMap([
                [1, false, 0, true, $this->product],
                [1, false, 1, true, $this->product1],
                [1, false, 2, true, $this->product2],
                [1, false, 5, true, $this->product5]
            ]));
        $this->dbStorage = $this->createPartialMock(DbStorage::class, ['deleteEntitiesFromStores']);
        $this->event = $this->createPartialMock(Event::class, ['getProduct']);
        $this->event->expects($this->any())->method('getProduct')->willReturn($this->product);
        $this->observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->productUrlRewriteGenerator = $this->createPartialMock(
            ProductUrlRewriteGenerator::class,
            ['generate']
        );
        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnValue([1 => 'rewrite']));
        $this->productScopeRewriteGenerator = $this->createPartialMock(
            ProductScopeRewriteGenerator::class,
            ['isGlobalScope']
        );
        $this->productScopeRewriteGenerator->expects($this->any())
            ->method('isGlobalScope')
            ->will($this->returnValueMap([
                [null, true],
                [0, true],
                [1, false],
                [2, false],
                [5, false],
            ]));
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->website1 = $this->createPartialMock(Website::class, ['getWebsiteId']);
        $this->website1->expects($this->any())->method('getWebsiteId')->willReturn(1);
        $this->website2 = $this->createPartialMock(Website::class, ['getWebsiteId']);
        $this->website2->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $this->storeManager->expects($this->any())
            ->method('getWebsites')
            ->will($this->returnValue([$this->website1, $this->website2]));

        $this->storeWebsiteRelation = $this->createPartialMock(
            StoreWebsiteRelationInterface::class,
            ['getStoreByWebsiteId']
        );
        $this->storeWebsiteRelation->expects($this->any())
            ->method('getStoreByWebsiteId')
            ->will($this->returnValueMap([[1, [1]], [2, [2, 5]]]));

        $this->model = $this->objectManager->getObject(
            ProductProcessUrlRewriteSavingObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
                'storeManager' => $this->storeManager,
                'storeWebsiteRelation' => $this->storeWebsiteRelation,
                'productRepository' => $this->productRepository,
                'dbStorage' => $this->dbStorage,
                'productScopeRewriteGenerator' => $this->productScopeRewriteGenerator
            ]
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function urlKeyDataProvider()
    {
        return [
            'url changed' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => [
                    '0' => true,
                    '1' => true,
                    '2' => true,
                    '5' => true
                ],
                'productInWebsites'     => [1],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [2, 5],

            ],
            'no changes' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => [
                    '0' => true,
                    '1' => true,
                    '2' => true,
                    '5' => true
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 0,
                'expectedRemoves'       => [],
            ],
            'visibility changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => [
                    '0' => true,
                    '1' => true,
                    '2' => true,
                    '5' => true
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [],
            ],
            'websites changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => true,
                'isChangedCategories'   => false,
                'visibilityResult'      => [
                    '0' => true,
                    '1' => true,
                    '2' => true,
                    '5' => true
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [],
            ],
            'categories changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => true,
                'visibilityResult'      => [
                    '0' => true,
                    '1' => true,
                    '2' => true,
                    '5' => true
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [],
            ],
            'url changed invisible' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => [
                    '0' => false,
                    '1' => false,
                    '2' => false,
                    '5' => false
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [1,2,5],
            ],
        ];
    }

    /**
     * @param bool $isChangedUrlKey
     * @param bool $isChangedVisibility
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param array $visibilityResult
     * @param int $productInWebsites
     * @param int $expectedReplaceCount
     * @param array $expectedRemoves
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $isChangedUrlKey,
        $isChangedVisibility,
        $isChangedWebsites,
        $isChangedCategories,
        $visibilityResult,
        $productInWebsites,
        $expectedReplaceCount,
        $expectedRemoves
    ) {
        $this->product->expects($this->any())->method('getStoreId')->will(
            $this->returnValue(Store::DEFAULT_STORE_ID)
        );
        $this->product->expects($this->any())->method('getWebsiteIds')->will(
            $this->returnValue($productInWebsites)
        );

        $this->product->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValueMap(
                [
                    ['visibility', $isChangedVisibility],
                    ['url_key', $isChangedUrlKey]
                ]
            ));

        $this->product->expects($this->any())
            ->method('getIsChangedWebsites')
            ->will($this->returnValue($isChangedWebsites));

        $this->product->expects($this->any())
            ->method('getIsChangedCategories')
            ->will($this->returnValue($isChangedCategories));

        $this->product->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($visibilityResult['0']));
        $this->product1->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($visibilityResult['1']));
        $this->product2->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($visibilityResult['2']));
        $this->product5->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($visibilityResult['5']));

        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with([1 => 'rewrite']);

        $this->dbStorage->expects($this->any())
            ->method('deleteEntitiesFromStores')
            ->with(
                $expectedRemoves,
                [1],
                ProductUrlRewriteGenerator::ENTITY_TYPE
            );

        $this->model->execute($this->observer);
    }
}

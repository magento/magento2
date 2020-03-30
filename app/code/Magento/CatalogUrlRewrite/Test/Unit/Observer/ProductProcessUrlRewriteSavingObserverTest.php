<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\Storage\DeleteEntitiesFromStores;
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
     * @var array
     */
    private $websites;

    /**
     * @var array
     */
    private $stores;

    /**
     * @var StoreWebsiteRelationInterface|MockObject
     */
    private $storeWebsiteRelation;

    /**
     * @var DeleteEntitiesFromStores|MockObject
     */
    private $deleteEntitiesFromStores;

    /**
     * @var Collection|MockObject
     */
    private $productCollection;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * Set up
     * Website_ID = 0 -> Store_ID = 0
     * Website_ID = 1 -> Store_ID = 1
     * Website_ID = 2 -> Store_ID = 2 & 5
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->urlPersist = $this->createMock(UrlPersistInterface::class);

        $this->websites[0] = $this->initialiseWebsite(0);
        $this->websites[1] = $this->initialiseWebsite(1);
        $this->websites[2] = $this->initialiseWebsite(2);

        $this->stores[0] = $this->initialiseStore(0, 0);
        $this->stores[1] = $this->initialiseStore(1, 1);
        $this->stores[2] = $this->initialiseStore(2, 2);
        $this->stores[5] = $this->initialiseStore(5, 2);

        $this->product = $this->initialiseProduct();

        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->productCollection = $this->createPartialMock(
            Collection::class,
            ['getAllAttributeValues']
        );
        $this->collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->productCollection);

        $this->deleteEntitiesFromStores = $this->createPartialMock(
            DeleteEntitiesFromStores::class,
            ['execute']
        );

        $this->event = $this->createPartialMock(Event::class, ['getProduct']);
        $this->event->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->product);

        $this->observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);

        $this->productUrlRewriteGenerator = $this->createPartialMock(
            ProductUrlRewriteGenerator::class,
            ['generate']
        );
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

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())
            ->method('getWebsites')
            ->will($this->returnValue([$this->websites[1], $this->websites[2]]));
        $this->storeManager->expects($this->any())
            ->method('getStores')
            ->will($this->returnValue([$this->stores[1], $this->stores[2], $this->stores[5]]));

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
                'deleteEntitiesFromStores' => $this->deleteEntitiesFromStores,
                'productScopeRewriteGenerator' => $this->productScopeRewriteGenerator,
                'collectionFactory' => $this->collectionFactory
            ]
        );
    }

    /**
     * Initialise product for test
     *
     * @return MockObject
     */
    public function initialiseProduct()
    {
        $product = $this->createPartialMock(
            Product::class,
            [
                'getId',
                'dataHasChangedFor',
                'isVisibleInSiteVisibility',
                'getIsChangedWebsites',
                'getIsChangedCategories',
                'getStoreId',
                'getWebsiteIds',
                'getStore'
            ]
        );
        $product->expects($this->any())->method('getId')->will($this->returnValue(1));
        return $product;
    }

    /**
     * Initialise website for test
     *
     * @param $websiteId
     * @return MockObject
     */
    public function initialiseWebsite($websiteId)
    {
        $website = $this->createPartialMock(Website::class, ['getWebsiteId']);
        $website->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        return $website;
    }

    /**
     * Initialise store for test
     *
     * @param $storeId
     * @return mixed
     */
    public function initialiseStore($storeId, $websiteId)
    {
        $store = $this->createPartialMock(Store::class, ['getStoreId','getWebsiteId']);
        $store->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $store->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        return $store;
    }

    /**
     * Data provider
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function urlKeyDataProvider()
    {
        return [
            //url has changed, so we would expect to see a replace issued
            //and the urls removed from the stores the product is not in
            //i.e stores belonging to website 2
            'global_scope_url_changed' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_BOTH,
                        1 => Product\Visibility::VISIBILITY_BOTH,
                        2 => Product\Visibility::VISIBILITY_BOTH,
                        5 => Product\Visibility::VISIBILITY_BOTH,
                    ],
                ],
                'productInWebsites'     => [1],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [2, 5],

            ],
            //Nothing has changed, so no replaces or removes
            'global_scope_no_changes' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_BOTH,
                        1 => Product\Visibility::VISIBILITY_BOTH,
                        2 => Product\Visibility::VISIBILITY_BOTH,
                        5 => Product\Visibility::VISIBILITY_BOTH,
                    ],
                ],
                'productInWebsites'     => [1],
                'expectedReplaceCount'  => 0,
                'expectedRemoves'       => [],
            ],
            //Product passed in had global scope set, but the visibility
            //at local scope for store 2 is false. Expect to see refresh
            //of urls and removal from store 2
            'global_scope_visibility_changed_local' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_BOTH,
                        1 => Product\Visibility::VISIBILITY_BOTH,
                        2 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        5 => Product\Visibility::VISIBILITY_BOTH,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [2],
            ],
            //Product passed in had global scope set, but the visibility
            //for all stores is false. Expect to see removal from stores 1,2 and 5
            'global_scope_visibility_changed_global' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        1 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        2 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        5 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 0,
                'expectedRemoves'       => [1, 2, 5],
            ],
            //Product has changed websites. Now in websites 1 and 2
            //We would expect to see a replace but no removals as the
            //product is in all stores
            'global_scope_websites_changed' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => true,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_BOTH,
                        1 => Product\Visibility::VISIBILITY_BOTH,
                        2 => Product\Visibility::VISIBILITY_BOTH,
                        5 => Product\Visibility::VISIBILITY_BOTH,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [],
            ],
            //Global scope, all visible, categories changed.
            //Expect to see replace and no removals.
            'global_scope_categories_changed' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => true,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_BOTH,
                        1 => Product\Visibility::VISIBILITY_BOTH,
                        2 => Product\Visibility::VISIBILITY_BOTH,
                        5 => Product\Visibility::VISIBILITY_BOTH,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [],
            ],
            //Global scope, url key has changed but products are
            //invisible in all stores, therefore remove any urls if
            //they exist.
            'global_scope_url_changed_invisible' => [
                'productScope'          => 0,
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        1 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        2 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        5 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [1, 2, 5],
            ],
            //local scope tests should only adjust URLs for local scope
            //Even if there are changes to the same product in other stores
            //they should be ignored. Here product in store 2 has been set
            //visible. Do not expect to see any removals for the other stores.
            'local_scope_visibility_changed_local_1' => [
                'productScope'          => 2,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        1 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        2 => Product\Visibility::VISIBILITY_BOTH,
                        5 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 1,
                'expectedRemoves'       => [],
            ],
            //Local scope, so only expecting to operate on store 2.
            //Product has been set invisible, removal expected.
            'local_scope_visibility_changed_local_2' => [
                'productScope'          => 2,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_BOTH,
                        1 => Product\Visibility::VISIBILITY_BOTH,
                        2 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        5 => Product\Visibility::VISIBILITY_BOTH,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 0,
                'expectedRemoves'       => [2],
            ],
            //Local scope, so only operate on store 5.
            //Visibility is false, so see only removal from
            //store 5.
            'local_scope_visibility_changed_global' => [
                'productScope'          => 5,
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibility'            => [
                    1 => [
                        0 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        1 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        2 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        5 => Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    ],
                ],
                'productInWebsites'     => [1, 2],
                'expectedReplaceCount'  => 0,
                'expectedRemoves'       => [5],
            ],
        ];
    }

    /**
     * @param int $productScope
     * @param bool $isChangedUrlKey
     * @param bool $isChangedVisibility
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param array $visibility
     * @param int $productInWebsites
     * @param int $expectedReplaceCount
     * @param array $expectedRemoves
     * @throws UrlAlreadyExistsException
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $productScope,
        $isChangedUrlKey,
        $isChangedVisibility,
        $isChangedWebsites,
        $isChangedCategories,
        $visibility,
        $productInWebsites,
        $expectedReplaceCount,
        $expectedRemoves
    ) {
        $this->product->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue($productInWebsites));
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
            ->method('getStoreId')
            ->willReturn($productScope);
        $this->product->expects($this->any())
            ->method('getStore')
            ->willReturn($this->stores[$productScope]);

        $this->productCollection->expects($this->any())
            ->method('getAllAttributeValues')
            ->will($this->returnValue($visibility));

        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($expectedReplaceCount > 0 ? ['test'] : []));
        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with($expectedReplaceCount > 0 ? ['test'] : []);

        $this->deleteEntitiesFromStores->expects($this->any())
            ->method('execute')
            ->with(
                $expectedRemoves,
                [1],
                ProductUrlRewriteGenerator::ENTITY_TYPE
            );

        $this->model->execute($this->observer);
    }
}

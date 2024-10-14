<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\GetVisibleForStores;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\CatalogUrlRewrite\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreResolver\GetStoresListByWebsiteIds;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessUrlRewriteSavingObserverTest extends TestCase
{
    /**
     * @var UrlPersistInterface|MockObject
     */
    protected $urlPersist;

    /**
     * @var Event|MockObject
     */
    protected $event;

    /**
     * @var Observer|MockObject
     */
    protected $observer;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var ProductProcessUrlRewriteSavingObserver
     */
    protected $model;

    /**
     * @var AppendUrlRewritesToProducts|MockObject
     */
    private $appendRewrites;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var MockObject|StoreViewService
     */
    private $storeViewService;

    /**
     * @var AttributeValue|MockObject
     */
    private $attributeValue;

    /**
     * @var UrlRewriteFinder|MockObject
     */
    private $urlRewriteFinder;

    /**
     * @var GetVisibleForStores|MockObject
     */
    private $visibilityForStores;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlPersist = $this->getMockForAbstractClass(UrlPersistInterface::class);
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreIds'])
            ->getMockForAbstractClass();
        $this->event = $this->getMockBuilder(Event::class)
            ->addMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->expects($this->any())->method('getProduct')->willReturn($this->product);
        $this->observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->appendRewrites = $this->getMockBuilder(AppendUrlRewritesToProducts::class)
            ->onlyMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $getStoresList = $this->getMockBuilder(GetStoresListByWebsiteIds::class)
            ->onlyMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeViewService = $this->createMock(StoreViewService::class);

        $this->attributeValue = $this->createMock(AttributeValue::class);
        $this->urlRewriteFinder = $this->createMock(UrlRewriteFinder::class);
        $this->visibilityForStores = $this->createMock(GetVisibleForStores::class);

        $this->model = new ProductProcessUrlRewriteSavingObserver(
            $this->urlPersist,
            $this->appendRewrites,
            $this->scopeConfig,
            $getStoresList,
            $this->storeViewService,
            $this->urlRewriteFinder,
            $this->visibilityForStores
        );
    }

    /**
     * Data provider
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function urlKeyDataProvider()
    {
        return [
            'websites changed' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 1,
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'website_ids' => [1, 2],
                ],
                'expectedExecutionCount' => 1,
            ],
            'url changed' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 1,
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'url_key' => 'simple1',
                ],
                'expectedExecutionCount' => 1,
            ],
            'no changes' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 1,
                    'is_changed_categories' => null,
                ],
                'newData' => [],
                'expectedExecutionCount' => 0,
            ],
            'visibility changed' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 1,
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'visibility' => Visibility::VISIBILITY_IN_CATALOG,
                ],
                'expectedExecutionCount' => 1,
            ],
            'categories changed' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 1,
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'is_changed_categories' => true,
                ],
                'expectedExecutionCount' => 1,
            ],
            'url changed with visibility - invisible' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                    'website_ids' => [1],
                    'store_id' => 1,
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'url_key' => 'simple1',
                ],
                'expectedExecutionCount' => 0,
            ],
            'visibility changed to invisible in global scope - 1' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 0,
                    'store_ids' => [1, 2],
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                ],
                'expectedExecutionCount' => 1,
                'expectedStoresToAdd' => [],
                'doesEntityHaveOverriddenVisibilityForStore' => [
                    1 => false,
                    2 => false,
                ],
                'expectedStoresToRemove' => [1, 2]
            ],
            'visibility changed to invisible in global scope - 2' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_BOTH,
                    'website_ids' => [1],
                    'store_id' => 0,
                    'store_ids' => [1, 2],
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                ],
                'expectedExecutionCount' => 1,
                'expectedStoresToAdd' => [],
                'doesEntityHaveOverriddenVisibilityForStore' => [
                    1 => false,
                    2 => true,
                ],
                'expectedStoresToRemove' => [1]
            ],
            'visibility changed from invisible to visible in global scope - 1' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                    'website_ids' => [1],
                    'store_id' => 0,
                    'store_ids' => [1, 2],
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'visibility' => Visibility::VISIBILITY_BOTH,
                ],
                'expectedExecutionCount' => 1,
                'expectedStoresToAdd' => [1, 2],
                'doesEntityHaveOverriddenVisibilityForStore' => [
                    1 => false,
                    2 => false,
                ]
            ],
            'visibility changed from invisible to visible in global scope - 2' => [
                'origData' => [
                    'entity_id' => 101,
                    'id' => 101,
                    'url_key' => 'simple',
                    'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                    'website_ids' => [1],
                    'store_id' => 0,
                    'store_ids' => [1, 2],
                    'is_changed_categories' => null,
                ],
                'newData' => [
                    'visibility' => Visibility::VISIBILITY_BOTH,
                ],
                'expectedExecutionCount' => 1,
                'expectedStoresToAdd' => [1],
                'doesEntityHaveOverriddenVisibilityForStore' => [
                    1 => false,
                    2 => true,
                ]
            ],
        ];
    }

    /**
     * @param array $origData
     * @param array $newData
     * @param int $expectedExecutionCount
     * @param array $expectedStoresToAdd
     * @param array $doesEntityHaveOverriddenVisibilityForStore
     * @param array $expectedStoresToRemove
     * @throws UrlAlreadyExistsException
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        array $origData,
        array $newData,
        int $expectedExecutionCount,
        array $expectedStoresToAdd = [],
        array $doesEntityHaveOverriddenVisibilityForStore = [],
        array $expectedStoresToRemove = [],
    ) {
        $this->product->setData($origData);
        $this->product->setOrigData();
        $this->product->addData($newData);

        $currentData = array_merge($origData, $newData);

        $this->storeViewService
            ->method('doesEntityHaveOverriddenVisibilityForStore')
            ->willReturnMap(
                array_map(
                    function (int $storeId, bool $override) {
                        return [$storeId, $this->product->getId(), Product::ENTITY, $override];
                    },
                    array_keys($doesEntityHaveOverriddenVisibilityForStore),
                    $doesEntityHaveOverriddenVisibilityForStore
                )
            );
        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        if (!$expectedExecutionCount) {
            $this->appendRewrites->expects($this->never())
                ->method('execute');
        } else {
            $this->appendRewrites->expects($this->exactly($expectedExecutionCount))
                ->method('execute')
                ->with(
                    [$this->product],
                    $expectedStoresToAdd
                );
        }

        if ($expectedStoresToRemove) {
            $this->urlPersist->expects($this->once())
                ->method('deleteByData')
                ->with(
                    [
                        UrlRewrite::ENTITY_ID => $this->product->getId(),
                        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::STORE_ID => $expectedStoresToRemove,
                    ]
                );
        }

        $this->product->expects($this->any())
            ->method('getStoreIds')
            ->willReturn($currentData['store_ids'] ?? [1]);

        $this->model->execute($this->observer);
    }
}

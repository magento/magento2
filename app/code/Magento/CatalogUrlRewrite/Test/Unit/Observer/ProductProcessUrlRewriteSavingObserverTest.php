<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreResolver\GetStoresListByWebsiteIds;
use Magento\UrlRewrite\Model\UrlPersistInterface;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlPersist = $this->getMockForAbstractClass(UrlPersistInterface::class);
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['getIsChangedWebsites', 'getIsChangedCategories'])
            ->onlyMethods(
                [
                    'getId',
                    'dataHasChangedFor',
                    'getVisibility',
                    'getStoreId',
                    'getWebsiteIds',
                    'getOrigData',
                    'getCategoryCollection',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->any())->method('getId')->willReturn(3);
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

        $this->model = new ProductProcessUrlRewriteSavingObserver(
            $this->urlPersist,
            $this->appendRewrites,
            $this->scopeConfig,
            $getStoresList
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
                'isChangedUrlKey' => true,
                'isChangedVisibility' => false,
                'isChangedWebsites' => false,
                'isChangedCategories' => false,
                'visibilityResult' => 4,
                'expectedReplaceCount' => 1,
                'websitesWithProduct' => [1],

            ],
            'no chnages' => [
                'isChangedUrlKey' => false,
                'isChangedVisibility' => false,
                'isChangedWebsites' => false,
                'isChangedCategories' => false,
                'visibilityResult' => 4,
                'expectedReplaceCount' => 0,
                'websitesWithProduct' => [1],
            ],
            'visibility changed' => [
                'isChangedUrlKey' => false,
                'isChangedVisibility' => true,
                'isChangedWebsites' => false,
                'isChangedCategories' => false,
                'visibilityResult' => 4,
                'expectedReplaceCount' => 1,
                'websitesWithProduct' => [1],
            ],
            'websites changed' => [
                'isChangedUrlKey' => false,
                'isChangedVisibility' => false,
                'isChangedWebsites' => true,
                'isChangedCategories' => false,
                'visibilityResult' => 4,
                'expectedReplaceCount' => 1,
                'websitesWithProduct' => [1],
            ],
            'categories changed' => [
                'isChangedUrlKey' => false,
                'isChangedVisibility' => false,
                'isChangedWebsites' => false,
                'isChangedCategories' => true,
                'visibilityResult' => 4,
                'expectedReplaceCount' => 1,
                'websitesWithProduct' => [1],
            ],
            'url changed invisible' => [
                'isChangedUrlKey' => true,
                'isChangedVisibility' => false,
                'isChangedWebsites' => false,
                'isChangedCategories' => false,
                'visibilityResult' => 1,
                'expectedReplaceCount' => 0,
                'websitesWithProduct' => [1],
            ],
        ];
    }

    /**
     * @param bool $isChangedUrlKey
     * @param bool $isChangedVisibility
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param bool $visibilityResult
     * @param int $expectedReplaceCount
     * @param array $websitesWithProduct
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $isChangedUrlKey,
        $isChangedVisibility,
        $isChangedWebsites,
        $isChangedCategories,
        $visibilityResult,
        $expectedReplaceCount,
        $websitesWithProduct
    ) {
        $this->product->expects($this->any())->method('getStoreId')->willReturn(12);

        $this->product->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap(
                [
                    ['visibility', $isChangedVisibility],
                    ['url_key', $isChangedUrlKey],
                ]
            );

        $this->product->expects($this->any())
            ->method('getIsChangedWebsites')
            ->willReturn($isChangedWebsites);

        $this->product->expects($this->any())
            ->method('getIsChangedCategories')
            ->willReturn($isChangedCategories);

        $this->product->expects($this->any())->method('getWebsiteIds')->will(
            $this->returnValue($websitesWithProduct)
        );

        $this->product->expects($this->any())
            ->method('getVisibility')
            ->willReturn($visibilityResult);

        $this->product->expects($this->any())
            ->method('getOrigData')
            ->willReturn($isChangedWebsites ? [] : $websitesWithProduct);
        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        $this->appendRewrites->expects($this->exactly($expectedReplaceCount))
            ->method('execute');

        $this->model->execute($this->observer);
    }
}

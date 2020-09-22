<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
     * @var ProductUrlRewriteGenerator|MockObject
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductProcessUrlRewriteSavingObserver
     */
    protected $model;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->urlPersist = $this->getMockForAbstractClass(UrlPersistInterface::class);
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['getIsChangedWebsites', 'getIsChangedCategories'])
            ->onlyMethods(['getId', 'dataHasChangedFor', 'isVisibleInSiteVisibility', 'getStoreId'])
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
        $this->productUrlRewriteGenerator = $this->createPartialMock(
            ProductUrlRewriteGenerator::class,
            ['generate']
        );
        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->willReturn([3 => 'rewrite']);
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            ProductProcessUrlRewriteSavingObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist
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
                'visibilityResult'      => true,
                'expectedReplaceCount'  => 1,

            ],
            'no chnages' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedReplaceCount'  => 0
            ],
            'visibility changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => true,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedReplaceCount'  => 1
            ],
            'websites changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => true,
                'isChangedCategories'   => false,
                'visibilityResult'      => true,
                'expectedReplaceCount'  => 1
            ],
            'categories changed' => [
                'isChangedUrlKey'       => false,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => true,
                'visibilityResult'      => true,
                'expectedReplaceCount'  => 1
            ],
            'url changed invisible' => [
                'isChangedUrlKey'       => true,
                'isChangedVisibility'   => false,
                'isChangedWebsites'     => false,
                'isChangedCategories'   => false,
                'visibilityResult'      => false,
                'expectedReplaceCount'  => 0
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
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $isChangedUrlKey,
        $isChangedVisibility,
        $isChangedWebsites,
        $isChangedCategories,
        $visibilityResult,
        $expectedReplaceCount
    ) {
        $this->product->expects($this->any())->method('getStoreId')->willReturn(12);

        $this->product->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap([
                ['visibility', $isChangedVisibility],
                ['url_key', $isChangedUrlKey]
            ]);

        $this->product->expects($this->any())
            ->method('getIsChangedWebsites')
            ->willReturn($isChangedWebsites);

        $this->product->expects($this->any())
            ->method('getIsChangedCategories')
            ->willReturn($isChangedCategories);

        $this->product->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->willReturn($visibilityResult);

        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with([3 => 'rewrite']);

        $this->model->execute($this->observer);
    }
}

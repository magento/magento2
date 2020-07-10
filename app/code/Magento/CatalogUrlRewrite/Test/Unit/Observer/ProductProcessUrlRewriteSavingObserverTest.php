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
 * Test for \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessUrlRewriteSavingObserverTest extends TestCase
{
    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersistMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var ProductProcessUrlRewriteSavingObserver
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->urlPersistMock = $this->createMock(UrlPersistInterface::class);

        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getIsChangedWebsites', 'getIsChangedCategories'])
            ->onlyMethods(['getId', 'dataHasChangedFor', 'isVisibleInSiteVisibility', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getProduct'])
            ->getMock();
        $event->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($event);

        $productUrlRewriteGeneratorMock = $this->createPartialMock(
            ProductUrlRewriteGenerator::class,
            ['generate']
        );
        $productUrlRewriteGeneratorMock->expects($this->any())
            ->method('generate')
            ->willReturn([3 => 'rewrite']);

        $this->model = $objectManager->getObject(
            ProductProcessUrlRewriteSavingObserver::class,
            [
                'productUrlRewriteGenerator' => $productUrlRewriteGeneratorMock,
                'urlPersist' => $this->urlPersistMock,
            ]
        );
    }

    /**
     * Test execute url key
     *
     * @param bool $isChangedUrlKey
     * @param bool $isChangedWebsites
     * @param bool $isChangedCategories
     * @param int $expectedReplaceCount
     * @return void
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        bool $isChangedUrlKey,
        bool $isChangedWebsites,
        bool $isChangedCategories,
        int $expectedReplaceCount
    ): void {
        $this->productMock->expects($this->atMost(1))
            ->method('getStoreId')
            ->willReturn(12);

        $this->productMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->willReturnMap([
                ['url_key', $isChangedUrlKey]
            ]);

        $this->productMock->expects($this->atMost(1))
            ->method('getIsChangedWebsites')
            ->willReturn($isChangedWebsites);

        $this->productMock->expects($this->atMost(1))
            ->method('getIsChangedCategories')
            ->willReturn($isChangedCategories);

        $this->urlPersistMock->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with([3 => 'rewrite']);

        $this->model->execute($this->observerMock);
    }

    /**
     * Data provider for testExecuteUrlKey
     *
     * @return array
     */
    public function urlKeyDataProvider(): array
    {
        return [
            'url changed' => [
                'isChangedUrlKey' => true,
                'isChangedWebsites' => false,
                'isChangedCategories' => false,
                'expectedReplaceCount' => 1,
            ],
            'no changes' => [
                'isChangedUrlKey' => false,
                'isChangedWebsites' => false,
                'isChangedCategories' => false,
                'expectedReplaceCount' => 0,
            ],
            'websites changed' => [
                'isChangedUrlKey' => false,
                'isChangedWebsites' => true,
                'isChangedCategories' => false,
                'expectedReplaceCount' => 1,
            ],
            'categories changed' => [
                'isChangedUrlKey' => false,
                'isChangedWebsites' => false,
                'isChangedCategories' => true,
                'expectedReplaceCount' => 1,
            ],
        ];
    }
}

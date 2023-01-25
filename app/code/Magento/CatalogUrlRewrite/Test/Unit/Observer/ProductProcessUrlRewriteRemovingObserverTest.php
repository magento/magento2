<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteRemovingObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteRemovingObserver
 */
class ProductProcessUrlRewriteRemovingObserverTest extends TestCase
{
    /*
     * Stub product ID
     */
    private const STUB_PRODUCT_ID = 333;

    /**
     * Testable Object
     *
     * @var ProductProcessUrlRewriteRemovingObserver
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersistMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->observer = $this->objectManager->getObject(
            ProductProcessUrlRewriteRemovingObserver::class,
            [
                'urlPersist' => $this->urlPersistMock
            ]
        );
    }

    /**
     * Test for execute(), covers test case for removing product URLs from storage
     */
    public function testRemoveProductUrlsFromStorage(): void
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->productMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(self::STUB_PRODUCT_ID);

        $this->urlPersistMock->expects($this->once())
            ->method('deleteByData')
            ->with([
                UrlRewrite::ENTITY_ID => self::STUB_PRODUCT_ID,
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test for execute(), covers test case for removing product URLs when the product doesn't have an ID
     */
    public function testRemoveProductUrlsWithEmptyProductId()
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->productMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->urlPersistMock->expects($this->never())->method('deleteByData');

        $this->observer->execute($this->observerMock);
    }
}

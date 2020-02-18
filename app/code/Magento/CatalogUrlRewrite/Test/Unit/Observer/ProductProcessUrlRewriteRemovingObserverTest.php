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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test for Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteRemovingObserver
 */
class ProductProcessUrlRewriteRemovingObserverTest extends TestCase
{
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
    private $urlPersist;

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

        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            ProductProcessUrlRewriteRemovingObserver::class,
            [
                'urlPersist' => $this->urlPersist
            ]
        );
    }

    /**
     * Test for execute()
     */
    public function testRemoveProductUrlsFromStorage()
    {
        $productId = 333;

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
            ->willReturn($productId);

        $this->urlPersist->expects($this->once())
            ->method('deleteByData')
            ->with([
                UrlRewrite::ENTITY_ID => $productId,
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]);

        $this->observer->execute($this->observerMock);
    }
}

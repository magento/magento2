<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Observer;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;
use Magento\Bundle\Observer\SetAttributeTabBlockObserver;
use Magento\Catalog\Helper\Catalog;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SetAttributeTabBlockObserverTest
 *
 * Test setting attribute tab block for bundle products
 */
class SetAttributeTabBlockObserverTest extends TestCase
{
    /**
     * @var SetAttributeTabBlockObserver
     */
    private $observer;

    /**
     * @var Catalog|MockObject
     */
    private $helperCatalogMock;

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
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->helperCatalogMock = $this->createMock(Catalog::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $objectManager->getObject(
            SetAttributeTabBlockObserver::class,
            [
                'helperCatalog' => $this->helperCatalogMock
            ]
        );
    }

    /**
     * Test setting attribute tab block for bundle product
     */
    public function testAddingAttributeTabForBundleProduct()
    {
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->eventMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->helperCatalogMock->expects($this->once())
            ->method('setAttributeTabBlock')
            ->with(Attributes::class);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test setting attribute tab block for a non bundle product
     */
    public function testAddingAttributeTabForNonBundleProduct()
    {
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_VIRTUAL);
        $this->eventMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->helperCatalogMock->expects($this->never())
            ->method('setAttributeTabBlock');

        $this->observer->execute($this->observerMock);
    }
}

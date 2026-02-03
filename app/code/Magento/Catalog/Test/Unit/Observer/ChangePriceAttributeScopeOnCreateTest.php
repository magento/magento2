<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Observer\ChangePriceAttributeScopeOnCreate;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Catalog\Observer\ChangePriceAttributeScopeOnCreate
 */
class ChangePriceAttributeScopeOnCreateTest extends TestCase
{
    /**
     * @var ChangePriceAttributeScopeOnCreate
     */
    private ChangePriceAttributeScopeOnCreate $observer;

    /**
     * @var CatalogHelper|MockObject
     */
    private CatalogHelper|MockObject $catalogDataMock;

    /**
     * @var Observer|MockObject
     */
    private Observer|MockObject $observerMock;

    /**
     * @var Event|MockObject
     */
    private Event|MockObject $eventMock;

    /**
     * @var Attribute|MockObject
     */
    private Attribute|MockObject $attributeMock;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->catalogDataMock = $this->createMock(CatalogHelper::class);
        $this->observerMock = $this->createMock(Observer::class);

        // Event uses magic methods from DataObject, so we need to add getAttribute method
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttribute'])
            ->getMock();

        // Attribute uses magic properties, so we need to configure getId as property and methods
        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFrontendInput', 'setScope'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            ChangePriceAttributeScopeOnCreate::class,
            [
                'catalogData' => $this->catalogDataMock
            ]
        );
    }

    /**
     * Test that new price attribute gets website scope when price scope is set to website
     *
     * @return void
     */
    public function testExecuteSetsWebsiteScopeForNewPriceAttribute(): void
    {
        // Set getId as a property (accessed without parentheses in the code)
        $this->attributeMock->getId = null;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('price');

        $this->catalogDataMock
            ->expects($this->once())
            ->method('getPriceScope')
            ->willReturn(Store::PRICE_SCOPE_WEBSITE);

        $this->attributeMock
            ->expects($this->once())
            ->method('setScope')
            ->with(ProductAttributeInterface::SCOPE_WEBSITE_TEXT);

        $result = $this->observer->execute($this->observerMock);

        $this->assertSame($this->observer, $result);
    }

    /**
     * Test that new price attribute gets global scope when price scope is set to global
     *
     * @return void
     */
    public function testExecuteSetsGlobalScopeForNewPriceAttribute(): void
    {
        // Set getId as a property (accessed without parentheses in the code)
        $this->attributeMock->getId = null;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('price');

        $this->catalogDataMock
            ->expects($this->once())
            ->method('getPriceScope')
            ->willReturn(Store::PRICE_SCOPE_GLOBAL);

        $this->attributeMock
            ->expects($this->once())
            ->method('setScope')
            ->with(ProductAttributeInterface::SCOPE_GLOBAL_TEXT);

        $result = $this->observer->execute($this->observerMock);

        $this->assertSame($this->observer, $result);
    }

    /**
     * Test that existing price attribute (with ID) does not have scope changed
     *
     * @return void
     */
    public function testExecuteDoesNotChangeScopeForExistingPriceAttribute(): void
    {
        // Set getId as a property with a value (existing attribute)
        $this->attributeMock->getId = 123;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->once())
            ->method('getFrontendInput');

        $this->catalogDataMock
            ->expects($this->never())
            ->method('getPriceScope');

        $this->attributeMock
            ->expects($this->never())
            ->method('setScope');

        $result = $this->observer->execute($this->observerMock);

        $this->assertSame($this->observer, $result);
    }

    /**
     * Test that new non-price attribute does not have scope changed
     *
     * @return void
     */
    public function testExecuteDoesNotChangeScopeForNonPriceAttribute(): void
    {
        // Set getId as a property (new attribute)
        $this->attributeMock->getId = null;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('text');

        $this->catalogDataMock
            ->expects($this->never())
            ->method('getPriceScope');

        $this->attributeMock
            ->expects($this->never())
            ->method('setScope');

        $result = $this->observer->execute($this->observerMock);

        $this->assertSame($this->observer, $result);
    }

    /**
     * Test that new attribute with empty string frontend input does not have scope changed
     *
     * @return void
     */
    public function testExecuteDoesNotChangeScopeForEmptyFrontendInput(): void
    {
        // Set getId as a property (new attribute)
        $this->attributeMock->getId = null;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('');

        $this->catalogDataMock
            ->expects($this->never())
            ->method('getPriceScope');

        $this->attributeMock
            ->expects($this->never())
            ->method('setScope');

        $result = $this->observer->execute($this->observerMock);

        $this->assertSame($this->observer, $result);
    }

    /**
     * Test that new price attribute with non-standard price scope value defaults to global
     *
     * @return void
     */
    public function testExecuteSetsGlobalScopeForNonStandardPriceScopeValue(): void
    {
        // Set getId as a property (new attribute)
        $this->attributeMock->getId = null;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->attributeMock);

        $this->attributeMock
            ->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('price');

        // Return some unexpected value (not PRICE_SCOPE_WEBSITE)
        $this->catalogDataMock
            ->expects($this->once())
            ->method('getPriceScope')
            ->willReturn(999);

        $this->attributeMock
            ->expects($this->once())
            ->method('setScope')
            ->with(ProductAttributeInterface::SCOPE_GLOBAL_TEXT);

        $result = $this->observer->execute($this->observerMock);

        $this->assertSame($this->observer, $result);
    }
}

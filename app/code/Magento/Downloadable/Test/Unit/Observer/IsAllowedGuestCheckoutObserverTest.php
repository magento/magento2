<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Observer\IsAllowedGuestCheckoutObserver;
use Magento\Framework\App\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IsAllowedGuestCheckoutObserverTest extends TestCase
{
    private const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';

    private const STUB_STORE_ID = 1;

    /** @var IsAllowedGuestCheckoutObserver */
    private $isAllowedGuestCheckoutObserver;

    /**
     * @var MockObject|Config
     */
    private $scopeConfigMock;

    /**
     * @var MockObject|DataObject
     */
    private $resultMock;

    /**
     * @var MockObject|Event
     */
    private $eventMock;

    /**
     * @var MockObject|Observer
     */
    private $observerMock;

    /**
     * @var MockObject|DataObject
     */
    private $storeMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createPartialMock(Config::class, ['isSetFlag', 'getValue']);

        $this->resultMock = $this->createPartialMock(
            \Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper::class,
            ['setIsAllowed']
        );

        $this->eventMock = $this->createPartialMock(
            \Magento\Framework\Event\Test\Unit\Helper\EventTestHelper::class,
            ['getStore', 'getResult', 'getQuote', 'getOrder']
        );

        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);

        $this->storeMock = $this->createPartialMock(
            \Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper::class,
            ['getId']
        );

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')
            ->with($this->storeMock)
            ->willReturn($this->storeMock);

        $this->isAllowedGuestCheckoutObserver = (new ObjectManagerHelper($this))
            ->getObject(
                IsAllowedGuestCheckoutObserver::class,
                [
                    'scopeConfig' => $this->scopeConfigMock,
                    'storeManager'=> $this->storeManagerMock
                ]
            );
    }

    /**
     * @param $productType
     * @param $isAllowed
     *
     * @return void
     */
    #[DataProvider('dataProviderForTestisAllowedGuestCheckoutConfigSetToTrue')]
    public function testIsAllowedGuestCheckoutConfigSetToTrue($productType, $isAllowed): void
    {
        if ($isAllowed) {
            $this->resultMock
                ->method('setIsAllowed')
                ->with(false);
        }

        $product = $this->createPartialMock(Product::class, ['getTypeId']);

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $item = $this->createPartialMock(QuoteItem::class, ['getProduct']);

        $item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $quote = $this->createPartialMock(Quote::class, ['getAllItems']);

        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$item]);

        $this->eventMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->method('getId')->willReturn(self::STUB_STORE_ID);

        $this->eventMock->expects($this->once())
            ->method('getResult')
            ->willReturn($this->resultMock);

        $this->eventMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                self::XML_PATH_DISABLE_GUEST_CHECKOUT,
                ScopeInterface::SCOPE_STORE,
                self::STUB_STORE_ID
            )
            ->willReturn(true);

        $this->observerMock->expects($this->exactly(3))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->assertInstanceOf(
            IsAllowedGuestCheckoutObserver::class,
            $this->isAllowedGuestCheckoutObserver->execute($this->observerMock)
        );
    }

    /**
     * @return array
     */
    public static function dataProviderForTestisAllowedGuestCheckoutConfigSetToTrue(): array
    {
        return [
            1 => [Type::TYPE_DOWNLOADABLE, true],
            2 => ['unknown', false]
        ];
    }

    /**
     * @return void
     */
    public function testIsAllowedGuestCheckoutConfigSetToFalse(): void
    {
        $product = $this->createPartialMock(Product::class, ['getTypeId']);

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE);

        $item = $this->createPartialMock(QuoteItem::class, ['getProduct']);

        $item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $quote = $this->createPartialMock(Quote::class, ['getAllItems']);

        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$item]);

        $this->eventMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->method('getId')->willReturn(self::STUB_STORE_ID);

        $this->eventMock->expects($this->once())
            ->method('getResult')
            ->willReturn($this->resultMock);

        $this->eventMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                self::XML_PATH_DISABLE_GUEST_CHECKOUT,
                ScopeInterface::SCOPE_STORE,
                self::STUB_STORE_ID
            )
            ->willReturn(false);

        $this->observerMock->expects($this->exactly(3))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->assertInstanceOf(
            IsAllowedGuestCheckoutObserver::class,
            $this->isAllowedGuestCheckoutObserver->execute($this->observerMock)
        );
    }
}

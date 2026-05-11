<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface as ProductOption;
use Magento\Catalog\Model\Plugin\QuoteItemProductOption as QuoteItemProductOptionPlugin;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractQuoteItem;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteItemProductOptionTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var QuoteItemProductOptionPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteToOrderItem|MockObject
     */
    private $subjectMock;

    /**
     * @var AbstractQuoteItem|MockObject
     */
    private $quoteItemMock;

    /**
     * @var QuoteItemOption|MockObject
     */
    private $quoteItemOptionMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(QuoteToOrderItem::class);
        
        $this->quoteItemMock = $this->createPartialMockWithReflection(
            AbstractQuoteItem::class,
            ['setOptions', 'getOptions', 'setProduct', 'getProduct', 'getQuote', 'getAddress', 'getOptionByCode']
        );
        $itemData = [];
        $quoteItem = $this->quoteItemMock;
        $this->quoteItemMock->method('setOptions')->willReturnCallback(function ($value) use (&$itemData, $quoteItem) {
            $itemData['options'] = $value;
            return $quoteItem;
        });
        $this->quoteItemMock->method('getOptions')->willReturnCallback(function () use (&$itemData) {
            return $itemData['options'] ?? null;
        });
        $this->quoteItemMock->method('setProduct')->willReturnCallback(function ($value) use (&$itemData, $quoteItem) {
            $itemData['product'] = $value;
            return $quoteItem;
        });
        $this->quoteItemMock->method('getProduct')->willReturnCallback(function () use (&$itemData) {
            return $itemData['product'] ?? null;
        });
        $this->quoteItemMock->method('getQuote')->willReturn(null);
        $this->quoteItemMock->method('getAddress')->willReturn(null);
        $this->quoteItemMock->method('getOptionByCode')->willReturn(null);
        
        $this->quoteItemOptionMock = $this->createPartialMock(QuoteItemOption::class, []);
        $this->productMock = $this->createMock(Product::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(QuoteItemProductOptionPlugin::class);
    }

    public function testBeforeItemToOrderItemEmptyOptions()
    {
        $this->quoteItemMock->setOptions(null);
        $this->quoteItemMock->expects(static::once())
            ->method('getOptions')
            ->willReturn(null);

        $this->plugin->beforeConvert($this->subjectMock, $this->quoteItemMock);
    }

    public function testBeforeItemToOrderItemWithOptions()
    {
        $optionMock1 = $this->createPartialMockWithReflection(
            QuoteItemOption::class,
            ['setCode', 'getCode']
        );
        $code1 = null;
        $optionMock1->method('setCode')->willReturnCallback(function ($value) use (&$code1, $optionMock1) {
            $code1 = $value;
            return $optionMock1;
        });
        $optionMock1->method('getCode')->willReturnCallback(function () use (&$code1) {
            return $code1;
        });
        $optionMock1->setCode('someText_8');
        
        $optionMock2 = $this->createPartialMockWithReflection(
            QuoteItemOption::class,
            ['setCode', 'getCode']
        );
        $code2 = null;
        $optionMock2->method('setCode')->willReturnCallback(function ($value) use (&$code2, $optionMock2) {
            $code2 = $value;
            return $optionMock2;
        });
        $optionMock2->method('getCode')->willReturnCallback(function () use (&$code2) {
            return $code2;
        });
        $optionMock2->setCode('not_int_text');
        
        $this->quoteItemMock->setOptions([$optionMock1, $optionMock2]);
        $this->quoteItemMock->expects(static::exactly(2))
            ->method('getOptions')
            ->willReturn([$optionMock1, $optionMock2]);
        $optionMock1->expects(static::exactly(2))
            ->method('getCode')
            ->willReturn('someText_8');
        $optionMock2->expects(static::exactly(2))
            ->method('getCode')
            ->willReturn('not_int_text');
        $this->productMock->expects(static::once())
            ->method('getOptionById')
            ->willReturn(new DataObject(['type' => ProductOption::OPTION_TYPE_FILE]));
        $this->quoteItemMock->setProduct($this->productMock);
        $this->quoteItemMock->expects(static::once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->plugin->beforeConvert($this->subjectMock, $this->quoteItemMock);
    }
}

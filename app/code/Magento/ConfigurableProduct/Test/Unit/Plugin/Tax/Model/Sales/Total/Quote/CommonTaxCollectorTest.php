<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Tax\Model\Sales\Total\Quote;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\Tax\Model\Sales\Total\Quote\CommonTaxCollector as CommonTaxCollectorPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for CommonTaxCollector plugin
 */
class CommonTaxCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CommonTaxCollectorPlugin
     */
    private $commonTaxCollectorPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->commonTaxCollectorPlugin = $this->objectManager->getObject(CommonTaxCollectorPlugin::class);
    }

    /**
     * Test to apply Tax Class Id from child item for configurable product
     */
    public function testAfterMapItem()
    {
        $childTaxClassId = 10;

        /** @var Product|MockObject $childProductMock */
        $childProductMock = $this->createPartialMock(
            Product::class,
            ['getTaxClassId']
        );
        $childProductMock->method('getTaxClassId')->willReturn($childTaxClassId);
        /* @var AbstractItem|MockObject $quoteItemMock */
        $childQuoteItemMock = $this->createMock(
            AbstractItem::class
        );
        $childQuoteItemMock->method('getProduct')->willReturn($childProductMock);

        /** @var Product|MockObject $productMock */
        $productMock = $this->createPartialMock(
            Product::class,
            ['getTypeId']
        );
        $productMock->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        /* @var AbstractItem|MockObject $quoteItemMock */
        $quoteItemMock = $this->createPartialMock(
            AbstractItem::class,
            ['getProduct', 'getHasChildren', 'getChildren', 'getQuote', 'getAddress', 'getOptionByCode']
        );
        $quoteItemMock->method('getProduct')->willReturn($productMock);
        $quoteItemMock->method('getHasChildren')->willReturn(true);
        $quoteItemMock->method('getChildren')->willReturn([$childQuoteItemMock]);

        /* @var TaxClassKeyInterface|MockObject $taxClassObjectMock */
        $taxClassObjectMock = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $taxClassObjectMock->expects($this->once())->method('setValue')->with($childTaxClassId);

        /* @var QuoteDetailsItemInterface|MockObject $quoteDetailsItemMock */
        $quoteDetailsItemMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $quoteDetailsItemMock->method('getTaxClassKey')->willReturn($taxClassObjectMock);

        $this->commonTaxCollectorPlugin->afterMapItem(
            $this->createMock(CommonTaxCollector::class),
            $quoteDetailsItemMock,
            $this->createMock(QuoteDetailsItemInterfaceFactory::class),
            $quoteItemMock
        );
    }
}

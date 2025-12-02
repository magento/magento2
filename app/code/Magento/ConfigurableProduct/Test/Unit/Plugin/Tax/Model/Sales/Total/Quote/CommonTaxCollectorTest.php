<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Tax\Model\Sales\Total\Quote;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Quote\Test\Unit\Helper\AbstractItemTestHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\Tax\Model\Sales\Total\Quote\CommonTaxCollector as CommonTaxCollectorPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for CommonTaxCollector plugin
 */
class CommonTaxCollectorTest extends TestCase
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
        $this->objectManager = new ObjectManager($this);
        $this->commonTaxCollectorPlugin = $this->objectManager->getObject(CommonTaxCollectorPlugin::class);
    }

    /**
     * Test to apply Tax Class Id from child item for configurable product
     */
    public function testAfterMapItem()
    {
        $childTaxClassId = 10;

        /** @var ProductTestHelper $childProductMock */
        $childProductMock = new ProductTestHelper();
        $childProductMock->setTaxClassId($childTaxClassId);
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
        /* @var AbstractItemTestHelper $quoteItemMock */
        $quoteItemMock = new AbstractItemTestHelper($productMock, [$childQuoteItemMock]);

        /* @var TaxClassKeyInterface|MockObject $taxClassObjectMock */
        $taxClassObjectMock = $this->createMock(TaxClassKeyInterface::class);
        $taxClassObjectMock->expects($this->once())->method('setValue')->with($childTaxClassId);

        /* @var QuoteDetailsItemInterface|MockObject $quoteDetailsItemMock */
        $quoteDetailsItemMock = $this->createMock(QuoteDetailsItemInterface::class);
        $quoteDetailsItemMock->method('getTaxClassKey')->willReturn($taxClassObjectMock);

        $this->commonTaxCollectorPlugin->afterMapItem(
            $this->createMock(CommonTaxCollector::class),
            $quoteDetailsItemMock,
            $this->createMock(QuoteDetailsItemInterfaceFactory::class),
            $quoteItemMock
        );
    }
}

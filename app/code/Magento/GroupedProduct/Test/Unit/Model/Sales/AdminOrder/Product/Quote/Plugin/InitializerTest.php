<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Sales\AdminOrder\Product\Quote\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\GroupedProduct\Model\Sales\AdminOrder\Product\Quote\Plugin\Initializer as QuoteInitializerPlugin;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\AdminOrder\Product\Quote\Initializer as QuoteInitializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitializerTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteInitializerPlugin|MockObject
     */
    private $plugin;

    /**
     * @var QuoteInitializer|MockObject
     */
    private $initializer;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var QuoteItem|MockObject
     */
    private $quoteItem;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var DataObject|MockObject
     */
    private $config;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->initializer = $this->createMock(QuoteInitializer::class);
        $this->quote = $this->createPartialMock(Quote::class, ['addProduct']);
        $this->product = $this->createPartialMock(Product::class, ['getTypeId']);
        $this->quoteItem = $this->createMock(QuoteItem::class);
        $this->config = $this->createMock(DataObject::class);

        $this->plugin = $this->objectManagerHelper->getObject(
            QuoteInitializerPlugin::class
        );
    }

    public function testAfterInit()
    {
        $this->assertSame(
            $this->quoteItem,
            $this->plugin->afterInit($this->initializer, $this->quoteItem, $this->quote, $this->product, $this->config)
        );
    }
}

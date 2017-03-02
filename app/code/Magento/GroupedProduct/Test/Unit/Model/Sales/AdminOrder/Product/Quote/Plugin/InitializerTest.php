<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Sales\AdminOrder\Product\Quote\Plugin;

use Magento\GroupedProduct\Model\Sales\AdminOrder\Product\Quote\Plugin\Initializer as QuoteInitializerPlugin;
use Magento\Sales\Model\AdminOrder\Product\Quote\Initializer as QuoteInitializer;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class InitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteInitializerPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $plugin;

    /**
     * @var QuoteInitializer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initializer;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItem;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->initializer = $this->getMockBuilder(QuoteInitializer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = $this->getMockBuilder(Quote::class)
            ->setMethods(['addProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMock();
        $this->quoteItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

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

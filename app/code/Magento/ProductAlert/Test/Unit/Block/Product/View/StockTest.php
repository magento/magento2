<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Test\Unit\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\ProductAlert\Block\Product\View\Stock;
use Magento\ProductAlert\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Stock
 */
class StockTest extends TestCase
{
    /**
     * @var MockObject|Data
     */
    protected $_helper;

    /**
     * @var MockObject|Product
     */
    protected $_product;

    /**
     * @var MockObject|Registry
     */
    protected $_registry;

    /**
     * @var MockObject|Stock
     */
    protected $_block;

    /**
     * @var MockObject|Layout
     */
    protected $_layout;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_helper = $this->createPartialMock(
            Data::class,
            ['isStockAlertAllowed', 'getSaveUrl']
        );
        $this->_product = $this->createPartialMock(
            Product::class,
            ['isAvailable', 'getId', '__wakeup']
        );
        $this->_product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_registry = $this->getMockBuilder(
            Registry::class
        )->disableOriginalConstructor()->setMethods(
            ['registry']
        )->getMock();
        $this->_block = $objectManager->getObject(
            Stock::class,
            ['helper' => $this->_helper, 'registry' => $this->_registry]
        );
        $this->_layout = $this->createMock(Layout::class);
    }

    public function testSetTemplateStockUrlAllowed()
    {
        $this->_helper->expects($this->once())->method('isStockAlertAllowed')->will($this->returnValue(true));
        $this->_helper->expects(
            $this->once()
        )->method(
            'getSaveUrl'
        )->with(
            'stock'
        )->will(
            $this->returnValue('http://url')
        );

        $this->_product->expects($this->once())->method('isAvailable')->will($this->returnValue(false));

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue($this->_product)
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('path/to/template.phtml', $this->_block->getTemplate());
        $this->assertEquals('http://url', $this->_block->getSignupUrl());
    }

    /**
     * @param bool $stockAlertAllowed
     * @param bool $productAvailable
     * @dataProvider setTemplateStockUrlNotAllowedDataProvider
     */
    public function testSetTemplateStockUrlNotAllowed($stockAlertAllowed, $productAvailable)
    {
        $this->_helper->expects(
            $this->once()
        )->method(
            'isStockAlertAllowed'
        )->will(
            $this->returnValue($stockAlertAllowed)
        );
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_product->expects($this->any())->method('isAvailable')->will($this->returnValue($productAvailable));

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue($this->_product)
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }

    /**
     * @return array
     */
    public function setTemplateStockUrlNotAllowedDataProvider()
    {
        return [
            'stock alert not allowed' => [false, false],
            'product is available (no alert)' => [true, true],
            'stock alert not allowed and product is available' => [false, true]
        ];
    }

    public function testSetTemplateNoProduct()
    {
        $this->_helper->expects($this->once())->method('isStockAlertAllowed')->will($this->returnValue(true));
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue(null)
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }
}

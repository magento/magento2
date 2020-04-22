<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\ProductAlert\Block\Product\View\Price;
use Magento\ProductAlert\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Price
 */
class PriceTest extends TestCase
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
     * @var MockObject|Price
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
            ['isPriceAlertAllowed', 'getSaveUrl']
        );
        $this->_product = $this->getMockBuilder(Product::class)
            ->addMethods(['getCanShowPrice'])
            ->onlyMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_product->expects($this->any())->method('getId')->willReturn(1);
        $this->_registry = $this->getMockBuilder(
            Registry::class
        )->disableOriginalConstructor()
            ->setMethods(
            ['registry']
        )->getMock();
        $this->_block = $objectManager->getObject(
            Price::class,
            ['helper' => $this->_helper, 'registry' => $this->_registry]
        );
        $this->_layout = $this->createMock(Layout::class);
    }

    public function testSetTemplatePriceAlertAllowed()
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->willReturn(true);
        $this->_helper->expects(
            $this->once()
        )->method(
            'getSaveUrl'
        )->with(
            'price'
        )->willReturn(
            'http://url'
        );

        $this->_product->expects($this->once())->method('getCanShowPrice')->willReturn(true);

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->willReturn(
            $this->_product
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('path/to/template.phtml', $this->_block->getTemplate());
        $this->assertEquals('http://url', $this->_block->getSignupUrl());
    }

    /**
     * @param bool $priceAllowed
     * @param bool $showProductPrice
     *
     * @dataProvider setTemplatePriceAlertNotAllowedDataProvider
     */
    public function testSetTemplatePriceAlertNotAllowed($priceAllowed, $showProductPrice)
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->willReturn($priceAllowed);
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_product->expects($this->any())->method('getCanShowPrice')->willReturn($showProductPrice);

        $this->_registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->willReturn(
            $this->_product
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }

    /**
     * @return array
     */
    public function setTemplatePriceAlertNotAllowedDataProvider()
    {
        return [
            'price alert is not allowed' => [false, true],
            'no product price' => [true, false],
            'price alert is not allowed and no product price' => [false, false]
        ];
    }

    public function testSetTemplateNoProduct()
    {
        $this->_helper->expects($this->once())->method('isPriceAlertAllowed')->willReturn(true);
        $this->_helper->expects($this->never())->method('getSaveUrl');

        $this->_registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->willReturn(
            null
        );

        $this->_block->setLayout($this->_layout);
        $this->_block->setTemplate('path/to/template.phtml');

        $this->assertEquals('', $this->_block->getTemplate());
        $this->assertNull($this->_block->getSignupUrl());
    }
}

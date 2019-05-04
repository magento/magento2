<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Plugin\QuoteItemProductOption as QuoteItemProductOptionPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractQuoteItem;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface as ProductOption;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteItemProductOptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteItemProductOptionPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteToOrderItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var AbstractQuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemMock;

    /**
     * @var QuoteItemOption|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemOptionMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder(QuoteToOrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItemMock = $this->getMockBuilder(AbstractQuoteItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getProduct'])
            ->getMockForAbstractClass();
        $this->quoteItemOptionMock = $this->getMockBuilder(QuoteItemOption::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(QuoteItemProductOptionPlugin::class);
    }

    public function testBeforeItemToOrderItemEmptyOptions()
    {
        $this->quoteItemMock->expects(static::once())
            ->method('getOptions')
            ->willReturn(null);

        $this->plugin->beforeConvert($this->subjectMock, $this->quoteItemMock);
    }

    public function testBeforeItemToOrderItemWithOptions()
    {
        $this->quoteItemMock->expects(static::exactly(2))
            ->method('getOptions')
            ->willReturn([$this->quoteItemOptionMock, $this->quoteItemOptionMock]);
        $this->quoteItemOptionMock->expects(static::exactly(2))
            ->method('getCode')
            ->willReturnOnConsecutiveCalls('someText_8', 'not_int_text');
        $this->productMock->expects(static::once())
            ->method('getOptionById')
            ->willReturn(new DataObject(['type' => ProductOption::OPTION_TYPE_FILE]));
        $this->quoteItemMock->expects(static::once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->plugin->beforeConvert($this->subjectMock, $this->quoteItemMock);
    }
}

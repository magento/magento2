<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\ShippingAssignment;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ShippingAssignmentProcessorTest
 */
class ShippingAssignmentProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingAssignmentFactory|MockObject
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingProcessor|MockObject
     */
    private $shippingProcessor;

    /**
     * @var CartItemPersister|MockObject
     */
    private $cartItemPersister;

    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    protected function setUp()
    {
        $this->shippingAssignmentFactory = $this->getMockBuilder(ShippingAssignmentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->shippingProcessor = $this->getMockBuilder(ShippingProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartItemPersister = $this->getMockBuilder(CartItemPersister::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shippingAssignmentProcessor = new ShippingAssignmentProcessor(
            $this->shippingAssignmentFactory,
            $this->shippingProcessor,
            $this->cartItemPersister
        );
    }

    /**
     * Test saving shipping assignments with deleted cart items
     *
     * @covers \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::save
     */
    public function testSaveWithDeletedCartItems()
    {
        $shippingAssignment = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $shipping = $this->getMockForAbstractClass(ShippingInterface::class);
        $quoteId = 1;

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItem->expects(static::once())
            ->method('isDeleted')
            ->willReturn(true);
        $quoteItem->expects(static::once())
            ->method('getItemId')
            ->willReturn($quoteId);
        
        $quote->expects(static::once())
            ->method('getItemById')
            ->with($quoteId)
            ->willReturn(null);

        $shippingAssignment->expects(static::once())
            ->method('getItems')
            ->willReturn([$quoteItem]);
        $shippingAssignment->expects(static::once())
            ->method('getShipping')
            ->willReturn($shipping);

        $this->cartItemPersister->expects(static::never())
            ->method('save');

        $this->shippingProcessor->expects(static::once())
            ->method('save')
            ->with($shipping, $quote);

        $this->shippingAssignmentProcessor->save(
            $quote,
            $shippingAssignment
        );
    }
}

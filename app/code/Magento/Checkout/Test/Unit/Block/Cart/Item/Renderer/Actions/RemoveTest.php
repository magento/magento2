<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove;
use Magento\Checkout\Block\Cart\Item\Renderer\Context;
use Magento\Checkout\Helper\Cart;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Remove
     */
    protected $model;

    /** @var Cart|\PHPUnit_Framework_MockObject_MockObject */
    protected $cartHelperMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->cartHelperMock = $this->getMockBuilder('Magento\Checkout\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove',
            [
                'cartHelper' => $this->cartHelperMock,
            ]
        );
    }

    public function testGetConfigureUrl()
    {
        $json = '{json;}';

        /**
         * @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder('\Magento\Checkout\Block\Cart\Item\Renderer\Context')
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var AbstractItem|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item\AbstractItem')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $contextMock->expects($this->once())
            ->method('getQuoteItem')
            ->willReturn($itemMock);

        $this->cartHelperMock->expects($this->once())
            ->method('getDeletePostJson')
            ->with($itemMock)
            ->willReturn($json);

        $this->model->setItemContext($contextMock);
        $this->assertEquals($json, $this->model->getDeletePostJson());
    }
}

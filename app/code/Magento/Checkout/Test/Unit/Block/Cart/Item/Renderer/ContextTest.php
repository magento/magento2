<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Context();
    }

    public function testGetQuoteItem()
    {
        /**
         * @var \Magento\Quote\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item\AbstractItem')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model->setQuoteItem($itemMock);
        $this->assertEquals($itemMock, $this->model->getQuoteItem());
    }
}

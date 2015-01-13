<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\Email\Items;

class DefaultItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Block\Order\Email\Items\DefaultItem
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Block\Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item  */
    protected $itemMock;

    /**
     * Initialize required data
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->block = $this->objectManager->getObject(
            'Magento\Sales\Block\Order\Email\Items\DefaultItems',
            [
                'context' => $this->objectManager->getObject(
                        'Magento\Backend\Block\Template\Context',
                        ['layout' => $this->layoutMock]
                    )
            ]
        );

        $this->priceRenderBlock = $this->getMockBuilder('\Magento\Backend\Block\Template')
            ->disableOriginalConstructor()
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();

        $this->itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
    }

    public function testGetItemPrice()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_price')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));

        $this->assertEquals($html, $this->block->getItemPrice($this->itemMock));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Unit\Block\Order\Email\Items\Order\GroupedTest;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GroupedProduct\Block\Order\Email\Items\Order\Grouped
     */
    protected $groupedBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Block\Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Creditmemo\Item */
    protected $creditMemoItemMock;

    /**
     * Initialize required data
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();


        $this->groupedBlock = $this->objectManager->getObject(
            \Magento\GroupedProduct\Block\Order\Email\Items\Order\Grouped::class,
            [
                'context' => $this->objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $this->layoutMock]
                )
            ]
        );

        $this->priceRenderBlock = $this->getMockBuilder(\Magento\Backend\Block\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();


        $this->creditMemoItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
    }

    public function testGetItem()
    {
        $html = '$34.28';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_price')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->creditMemoItemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));

        $this->assertEquals($html, $this->groupedBlock->getItemPrice($this->creditMemoItemMock));
    }
}

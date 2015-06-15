<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab;

/**
 * Order History tab test
 */
class HistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Helper\Admin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adminHelperMock;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\View\Tab\History
     */
    protected $commentsHistory;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->coreRegistryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->adminHelperMock = $this->getMock('\Magento\Sales\Helper\Admin', [], [], '', false);

        $this->commentsHistory = $this->objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Order\View\Tab\History',
            [
                'adminHelper' => $this->adminHelperMock,
                'registry' => $this->coreRegistryMock
            ]
        );
    }

    public function testGetItemComment()
    {
        $expectation = 'Authorized amount of £20.00 Transaction ID: &quot;XXX123123XXX&quot;';
        $item['comment'] = 'Authorized amount of £20.00 Transaction ID: "XXX123123XXX"';
        $this->adminHelperMock->expects($this->once())
            ->method('escapeHtmlWithLinks')
            ->with($item['comment'], ['b', 'br', 'strong', 'i', 'u', 'a'])
            ->willReturn($expectation);
        $this->assertEquals($expectation, $this->commentsHistory->getItemComment($item));
    }

    public function testGetItemCommentIsNotSet()
    {
        $item = [];
        $this->adminHelperMock->expects($this->never())->method('escapeHtmlWithLinks');
        $this->assertEquals('', $this->commentsHistory->getItemComment($item));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab;

/**
 * Order History tab test
 */
class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Helper\Admin|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $adminHelperMock;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\View\Tab\History
     */
    protected $commentsHistory;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->adminHelperMock = $this->createMock(\Magento\Sales\Helper\Admin::class);

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLocaleDate'])
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMock();

        $this->contextMock->expects($this->any())->method('getLocaleDate')->willReturn(
            $this->localeDateMock
        );

        $this->commentsHistory = $this->objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\View\Tab\History::class,
            [
                'adminHelper' => $this->adminHelperMock,
                'registry' => $this->coreRegistryMock,
                'context' => $this->contextMock,
                'localeDate' => $this->localeDateMock
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

    public function testGetItemCreatedAtDate()
    {
        $date = new \DateTime;
        $item = ['created_at' => $date ];

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)
            ->willReturn('date');

        $this->assertEquals('date', $this->commentsHistory->getItemCreatedAt($item));
    }

    public function testGetItemCreatedAtTime()
    {
        $date = new \DateTime;
        $item = ['created_at' => $date ];

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::NONE, \IntlDateFormatter::MEDIUM)
            ->willReturn('time');

        $this->assertEquals('time', $this->commentsHistory->getItemCreatedAt($item, 'time'));
    }

    public function testGetItemCreatedAtEmpty()
    {
        $item = ['title' => "Test" ];

        $this->localeDateMock->expects($this->never())->method('formatDateTime');
        $this->assertEquals('', $this->commentsHistory->getItemCreatedAt($item));
        $this->assertEquals('', $this->commentsHistory->getItemCreatedAt($item, 'time'));
    }
}

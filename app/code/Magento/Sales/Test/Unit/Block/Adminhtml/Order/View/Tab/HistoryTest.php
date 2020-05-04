<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\History;
use Magento\Sales\Helper\Admin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Order History tab test
 */
class HistoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Admin|MockObject
     */
    protected $adminHelperMock;

    /**
     * @var History
     */
    protected $commentsHistory;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->adminHelperMock = $this->createMock(Admin::class);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLocaleDate'])
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();

        $this->contextMock->expects($this->any())->method('getLocaleDate')->willReturn(
            $this->localeDateMock
        );

        $this->commentsHistory = $this->objectManager->getObject(
            History::class,
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
        $date = new \DateTime();
        $item = ['created_at' => $date ];

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)
            ->willReturn('date');

        $this->assertEquals('date', $this->commentsHistory->getItemCreatedAt($item));
    }

    public function testGetItemCreatedAtTime()
    {
        $date = new \DateTime();
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

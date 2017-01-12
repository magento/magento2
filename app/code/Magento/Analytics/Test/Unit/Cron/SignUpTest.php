<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Model\AnalyticsConnector;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResource;
use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Cron\SignUp;
use Magento\AdminNotification\Model\Inbox;

/**
 * Class SignUpCommandTest
 */
class SignUpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AnalyticsConnector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsConnectorMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var InboxFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inboxFactoryMock;

    /**
     * @var Inbox|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inboxMock;

    /**
     * @var InboxResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inboxResourceMock;

    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var SignUp
     */
    private $signUp;

    protected function setUp()
    {
        $this->analyticsConnectorMock =  $this->getMockBuilder(AnalyticsConnector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock =  $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inboxFactoryMock =  $this->getMockBuilder(InboxFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inboxResourceMock =  $this->getMockBuilder(InboxResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagManagerMock =  $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inboxMock =  $this->getMockBuilder(Inbox::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signUp = new SignUp(
            $this->analyticsConnectorMock,
            $this->configWriterMock,
            $this->inboxFactoryMock,
            $this->inboxResourceMock,
            $this->flagManagerMock
        );
    }

    public function testExecute()
    {
        $attemptsCount = 10;

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($attemptsCount);

        $attemptsCount -= 1;
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $this->analyticsConnectorMock->expects($this->once())
            ->method('execute')
            ->with('signUp')
            ->willReturn(true);
        $this->configWriterMock->expects($this->once())
            ->method('delete')
            ->with(SubscriptionHandler::CRON_STRING_PATH)
            ->willReturn(true);
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        $this->assertTrue($this->signUp->execute());
    }

    public function testExecuteFlagNotExist()
    {
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(null);
        $this->configWriterMock->expects($this->once())
            ->method('delete')
            ->with(SubscriptionHandler::CRON_STRING_PATH)
            ->willReturn(true);
        $this->assertFalse($this->signUp->execute());
    }

    public function testExecuteZeroAttempts()
    {
        $attemptsCount = 0;
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($attemptsCount);
        $this->configWriterMock->expects($this->once())
            ->method('delete')
            ->with(SubscriptionHandler::CRON_STRING_PATH)
            ->willReturn(true);
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        $this->inboxFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->inboxMock);
        $this->inboxMock->expects($this->once())
            ->method('addNotice');
        $this->inboxResourceMock->expects($this->once())
            ->method('save')
            ->with($this->inboxMock);
        $this->assertFalse($this->signUp->execute());
    }
}

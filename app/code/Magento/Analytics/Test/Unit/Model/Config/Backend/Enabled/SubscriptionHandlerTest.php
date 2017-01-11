<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Config\Backend\Enabled;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\FlagManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SubscriptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenMock;

    /**
     * @var Value
     */
    private $configValue;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var int
     */
    private $attemptsInitValue = 10;

    /**
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->configValue = $this->objectManagerHelper->getObject(
            Value::class,
            [
                'config' => $this->configMock,
            ]
        );

        $this->subscriptionHandler = $this->objectManagerHelper->getObject(
            SubscriptionHandler::class,
            [
                'flagManager' => $this->flagManagerMock,
                'configWriter' => $this->configWriterMock,
                'attemptsInitValue' => $this->attemptsInitValue,
                'analyticsToken' => $this->tokenMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testProcessWithEnabledTrue()
    {
        $this->configValue->setValue(1);
        $this->configMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(0);
        $this->tokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);

        $this->configWriterMock
            ->expects($this->once())
            ->method('save');

        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue)
            ->willReturn(true);

        $this->assertTrue(
            $this->subscriptionHandler->process($this->configValue)
        );
    }


    /**
     * @return void
     */
    public function testProcessWithEnabledFalse()
    {
        $this->configValue->setValue(0);
        $this->configMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);

        $this->flagManagerMock
            ->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(true);

        $this->assertTrue(
            $this->subscriptionHandler->process($this->configValue)
        );
    }
}

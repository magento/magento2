<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Plugin;

use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Captcha\Plugin\ResetPaymentAttemptsAfterOrderIsPlacedPlugin;
use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Captcha\Model\DefaultModel;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ResetPaymentAttemptsAfterOrderIsPlacedPluginTest
 */
class ResetPaymentAttemptsAfterOrderIsPlacedPluginTest extends TestCase
{
    /**
     * Test that the method resets attempts for frontend checkout
     */
    public function testExecuteExpectsDeleteUserAttemptsCalled()
    {
        $orderManagementInterfaceMock = $this->getMockForAbstractClass(OrderManagementInterface::class);
        $resultOrderMock = $this->createMock(OrderInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects($this->once())->method('getCustomerEmail')->willReturn('email@example.com');
        $captchaModelMock = $this->createMock(DefaultModel::class);
        $captchaModelMock->expects($this->once())->method('setShowCaptchaInSession')->with(false)->willReturnSelf();
        $helperCaptchaMock = $this->createMock(HelperCaptcha::class);
        $helperCaptchaMock->expects($this->once())->method('getCaptcha')->willReturn($captchaModelMock);
        $logMock = $this->createMock(Log::class);
        $logMock->expects($this->once())->method('deleteUserAttempts')->willReturnSelf();
        $resLogFactoryMock = $this->createMock(LogFactory::class);
        $resLogFactoryMock->expects($this->once())->method('create')->willReturn($logMock);
        $observer = new ResetPaymentAttemptsAfterOrderIsPlacedPlugin($helperCaptchaMock, $resLogFactoryMock);
        $observer->afterPlace($orderManagementInterfaceMock, $resultOrderMock, $orderMock);
    }
}

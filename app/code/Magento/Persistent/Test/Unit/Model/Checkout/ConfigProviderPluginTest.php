<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Checkout;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Checkout\ConfigProviderPlugin;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigProviderPluginTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $maskFactoryMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var ConfigProviderPlugin
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->persistentHelperMock = $this->createMock(Data::class);
        $this->persistentSessionMock = $this->createMock(Session::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->maskFactoryMock = $this->createPartialMock(
            QuoteIdMaskFactory::class,
            ['create']
        );
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->subjectMock = $this->createMock(DefaultConfigProvider::class);

        $this->plugin = new ConfigProviderPlugin(
            $this->persistentHelperMock,
            $this->persistentSessionMock,
            $this->checkoutSessionMock,
            $this->maskFactoryMock,
            $this->customerSessionMock
        );
    }

    /**
     * @param bool $persistenceEnabled
     * @param bool $isPersistent
     * @param bool $isLoggedIn
     *
     * @dataProvider configDataProvider
     */
    public function testAfterGetConfigNegative($persistenceEnabled, $isPersistent, $isLoggedIn)
    {
        $result = [40, 30, 50];

        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->willReturn($persistenceEnabled);
        $this->persistentSessionMock->expects($this->any())->method('isPersistent')->willReturn($isPersistent);
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn($isLoggedIn);
        $this->maskFactoryMock->expects($this->never())->method('create');
        $this->assertEquals($result, $this->plugin->afterGetConfig($this->subjectMock, $result));
    }

    /**
     * @return array
     */
    public static function configDataProvider()
    {
        return [
            [false, true, true], //disabled persistence case
            [true, false, true], //persistence enabled but not persistent session
            [true, true, true],  //logged in user
        ];
    }

    public function testAfterGetConfigPositive()
    {
        $maskedId = 3005;
        $result = [40, 30, 50];
        $expectedResult = $result;
        $expectedResult['quoteData']['entity_id'] = $maskedId;

        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);

        $quoteMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getMaskedId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->maskFactoryMock->expects($this->once())->method('create')->willReturn($quoteMaskMock);
        $quoteMock = $this->createMock(Quote::class);

        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMaskMock->expects($this->once())->method('load')->willReturnSelf();
        $quoteMaskMock->expects($this->once())->method('getMaskedId')->willReturn($maskedId);
        $this->assertEquals($expectedResult, $this->plugin->afterGetConfig($this->subjectMock, $result));
    }
}

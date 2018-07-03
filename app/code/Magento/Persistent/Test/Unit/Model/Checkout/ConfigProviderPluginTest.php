<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Model\Checkout;

class ConfigProviderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $maskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\Checkout\ConfigProviderPlugin
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->persistentHelperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->maskFactoryMock = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create', '__wakeup']
        );
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->subjectMock = $this->createMock(\Magento\Checkout\Model\DefaultConfigProvider::class);

        $this->plugin = new \Magento\Persistent\Model\Checkout\ConfigProviderPlugin(
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
    public function configDataProvider()
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

        $quoteMaskMock = $this->createPartialMock(\Magento\Quote\Model\QuoteIdMask::class, ['load', 'getMaskedId']);
        $this->maskFactoryMock->expects($this->once())->method('create')->willReturn($quoteMaskMock);
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);

        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMaskMock->expects($this->once())->method('load')->willReturnSelf();
        $quoteMaskMock->expects($this->once())->method('getMaskedId')->willReturn($maskedId);
        $this->assertEquals($expectedResult, $this->plugin->afterGetConfig($this->subjectMock, $result));
    }
}

<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Helper;

use Codeception\Lib\ModuleContainer;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Magento\FunctionalTestingFramework\Module\MagentoActionProxies;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;
use Magento\Paypal\Test\Mftf\Helper\PayPalLoginHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify that login recovery only refills an active email field.
 */
class PayPalLoginHelperTest extends TestCase
{
    /**
     * @var MagentoWebDriver&MockObject
     */
    private MagentoWebDriver $magentoWebDriver;

    /**
     * @var MagentoActionProxies&MockObject
     */
    private MagentoActionProxies $actionProxies;

    /**
     * @var PayPalLoginHelper
     */
    private PayPalLoginHelper $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->magentoWebDriver = $this->createMock(MagentoWebDriver::class);
        $this->actionProxies = $this->createMock(MagentoActionProxies::class);
        $moduleContainer = $this->createMock(ModuleContainer::class);
        $moduleContainer->expects($this->atLeastOnce())
            ->method('hasModule')
            ->willReturnMap([
                ['\\' . MagentoWebDriver::class, true],
                ['\\' . MagentoActionProxies::class, true],
            ]);
        $moduleContainer->expects($this->atLeastOnce())
            ->method('getModule')
            ->willReturnMap([
                ['\\' . MagentoWebDriver::class, $this->magentoWebDriver],
                ['\\' . MagentoActionProxies::class, $this->actionProxies],
            ]);
        $this->helper = new PayPalLoginHelper($moduleContainer);
    }

    /**
     * Test that a cleared email field is refilled through the secret-aware WebDriver API.
     *
     * @covers \Magento\Paypal\Test\Mftf\Helper\PayPalLoginHelper::refillEmailIfStillOnEmailStep
     * @return void
     */
    public function testRefillEmailShouldUseEncryptedCredentialWhenEmailFieldIsVisible(): void
    {
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('isDisplayed')->willReturn(true);
        $this->magentoWebDriver->expects($this->exactly(2))
            ->method('_findElements')
            ->willReturnMap([
                ['#password', []],
                ['#email', [$element]],
            ]);
        $this->magentoWebDriver->expects($this->once())
            ->method('fillSecretField')
            ->with('#email', 'encrypted-email');
        $this->actionProxies->expects($this->once())
            ->method('getSecret')
            ->with('magento/PAYPAL_LOGIN')
            ->willReturn('encrypted-email');

        $this->helper->refillEmailIfStillOnEmailStep('#email', '#password', 'magento/PAYPAL_LOGIN');
    }

    /**
     * Test that advancing beyond the email step does not trigger a refill.
     *
     * @param bool $emailExists
     * @covers \Magento\Paypal\Test\Mftf\Helper\PayPalLoginHelper::refillEmailIfStillOnEmailStep
     * @return void
     */
    #[DataProvider('unavailableEmailProvider')]
    public function testRefillEmailShouldSkipWhenEmailFieldIsUnavailable(bool $emailExists): void
    {
        $elements = [];
        if ($emailExists) {
            $element = $this->createMock(RemoteWebElement::class);
            $element->expects($this->once())->method('isDisplayed')->willReturn(false);
            $elements[] = $element;
        }
        $this->magentoWebDriver->expects($this->exactly(2))
            ->method('_findElements')
            ->willReturnMap([
                ['#password', []],
                ['#email', $elements],
            ]);
        $this->magentoWebDriver->expects($this->never())->method('fillSecretField');
        $this->actionProxies->expects($this->never())->method('getSecret');

        $this->helper->refillEmailIfStillOnEmailStep('#email', '#password', 'magento/PAYPAL_LOGIN');
    }

    /**
     * Test that a password panel appearing during the slide animation prevents another email submission.
     *
     * @covers \Magento\Paypal\Test\Mftf\Helper\PayPalLoginHelper::refillEmailIfStillOnEmailStep
     * @return void
     */
    public function testRefillEmailShouldSkipWhenPasswordStepIsVisible(): void
    {
        $passwordElement = $this->createMock(RemoteWebElement::class);
        $passwordElement->expects($this->once())->method('isDisplayed')->willReturn(true);
        $this->magentoWebDriver->expects($this->once())
            ->method('_findElements')
            ->with('#password')
            ->willReturn([$passwordElement]);
        $this->magentoWebDriver->expects($this->never())->method('fillSecretField');
        $this->actionProxies->expects($this->never())->method('getSecret');

        $this->helper->refillEmailIfStillOnEmailStep('#email', '#password', 'magento/PAYPAL_LOGIN');
    }

    /**
     * Test that retrying Next requires an active email step and an actionable button.
     *
     * @param bool $passwordVisible
     * @param bool|null $nextVisible
     * @param bool $nextEnabled
     * @param bool $shouldClick
     * @covers \Magento\Paypal\Test\Mftf\Helper\PayPalLoginHelper::clickNextIfStillOnEmailStep
     * @return void
     */
    #[DataProvider('nextButtonStateProvider')]
    public function testRetryNextShouldRespectBothStepAndButtonState(
        bool $passwordVisible,
        ?bool $nextVisible,
        bool $nextEnabled,
        bool $shouldClick
    ): void {
        $passwordElements = [];
        if ($passwordVisible) {
            $passwordElement = $this->createStub(RemoteWebElement::class);
            $passwordElement->method('isDisplayed')->willReturn(true);
            $passwordElements[] = $passwordElement;
        }
        $nextElements = [];
        if ($nextVisible !== null) {
            $nextElement = $this->createStub(RemoteWebElement::class);
            $nextElement->method('isDisplayed')->willReturn($nextVisible);
            $nextElement->method('isEnabled')->willReturn($nextEnabled);
            $nextElements[] = $nextElement;
        }
        $this->magentoWebDriver->expects($this->exactly($passwordVisible ? 1 : 2))
            ->method('_findElements')
            ->willReturnMap([
                ['#password', $passwordElements],
                ['#next', $nextElements],
            ]);
        $this->magentoWebDriver->expects($shouldClick ? $this->once() : $this->never())
            ->method('click')
            ->with('#next');
        $this->actionProxies->expects($this->never())->method('getSecret');

        $this->helper->clickNextIfStillOnEmailStep('#next', '#password');
    }

    /**
     * Provide the supported email, transition and password states.
     *
     * @return array<string, array{bool, bool|null, bool, bool}>
     */
    public static function nextButtonStateProvider(): array
    {
        return [
            'neither control is present' => [false, null, false, false],
            'Next is hidden' => [false, false, true, false],
            'Next is disabled' => [false, true, false, false],
            'Next is visible and enabled' => [false, true, true, true],
            'password step is visible while Next remains visible' => [true, true, true, false],
        ];
    }

    /**
     * Provide the absent and hidden email states after the login advances.
     *
     * @return array<string, array{bool}>
     */
    public static function unavailableEmailProvider(): array
    {
        return [
            'email absent on password page' => [false],
            'email hidden on password page' => [true],
        ];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Plugin;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Block\Form\Container;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Plugin\PaymentMethodProcess;
use Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentMethodProcessTest extends TestCase
{
    /**
     * @const string
     */
    public const PAYMENT_METHOD_CHECKMO = 'checkmo';

    /**
     * @const string
     */
    public const PAYMENT_METHOD_PAYFLOWPRO_CC_VAULT = 'payflowpro_cc_vault';

    /**
     * @var TokensConfigProvider|MockObject
     */
    private TokensConfigProvider $tokensConfigProviderMock;

    /**
     * @var PaymentMethodProcess
     */
    private $subject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->tokensConfigProviderMock = $this->getMockBuilder(TokensConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = new PaymentMethodProcess($this->tokensConfigProviderMock);
    }

    /**
     * Test retrieve available payment methods
     *
     * @param \Closure|null $tokenInterface
     * @param int $availableMethodsCount
     * @dataProvider afterGetMethodsDataProvider
     */
    public function testAfterGetMethods($tokenInterface, $availableMethodsCount)
    {
        if ($tokenInterface!=null) {
            $tokenInterface = $tokenInterface($this);
        }
        $checkmoPaymentMethod = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMockForAbstractClass();
        $checkmoPaymentMethod->expects($this->any())->method('getCode')
            ->willReturn(self::PAYMENT_METHOD_CHECKMO);

        $payflowCCVaultTPaymentMethod = $this->getMockBuilder(VaultPaymentInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMockForAbstractClass();
        $payflowCCVaultTPaymentMethod->expects($this->any())->method('getCode')
            ->willReturn(self::PAYMENT_METHOD_PAYFLOWPRO_CC_VAULT);
        $methods = [$checkmoPaymentMethod, $payflowCCVaultTPaymentMethod];
        $containerMock = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokensConfigProviderMock->method('getTokensComponents')
            ->with(self::PAYMENT_METHOD_PAYFLOWPRO_CC_VAULT)
            ->willReturn($tokenInterface);

        $result = $this->subject->afterGetMethods($containerMock, $methods);
        $this->assertEquals($availableMethodsCount, count($result));
    }

    protected function getMockForTokenUiComponent() {
        $tokenUiComponentInterface = $this->getMockBuilder(TokenUiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $tokenUiComponentInterface;
    }

    /**
     * Data Provider
     */
    public static function afterGetMethodsDataProvider()
    {
        $tokenUiComponentInterface = static fn (self $testCase) => $testCase->getMockForTokenUiComponent();
        return [
            [null, 1],
            [$tokenUiComponentInterface, 2],
        ];
    }
}

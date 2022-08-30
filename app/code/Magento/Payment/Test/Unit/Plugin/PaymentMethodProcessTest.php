<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Block\Form\Container;
use Magento\Payment\Plugin\PaymentMethodProcess;
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
    public const PAYMENT_METHOD_BRAINTREE = 'braintree';

    /**
     * @const string
     */
    public const PAYMENT_METHOD_BRAINTREE_CC_VAULT = 'braintree_cc_vault';

    /**
     * @var TokensConfigProvider|MockObject
     */
    private TokensConfigProvider $tokensConfigProviderMock;

    /**
     * @var Container|MockObject
     */
    private $containerMock;

    /**
     * @var PaymentMethodProcess
     */
    private $plugin;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->tokensConfigProviderMock = $this->getMockBuilder(TokensConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->containerMock = $objectManagerHelper->getObject(Container::class);

        $this->plugin = $objectManagerHelper->getObject(
            PaymentMethodProcess::class,
            [
                'braintreeCCVault' => self::PAYMENT_METHOD_BRAINTREE_CC_VAULT,
                'tokensConfigProvider' => $this->tokensConfigProviderMock
            ]
        );
    }

    /**
     * @param array $methods
     * @param array $expectedResult
     * @param array $tokenComponents
     * @dataProvider afterGetMethodsDataProvider
     */
    public function testAfterGetMethods(array $methods, array $expectedResult, array $tokenComponents)
    {

        $this->tokensConfigProviderMock->method('getTokensComponents')
            ->with(self::PAYMENT_METHOD_BRAINTREE_CC_VAULT)
            ->willReturn($tokenComponents);

        $result = $this->plugin->afterGetMethods($this->containerMock, $methods);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Data provider for AfterGetMethods.
     *
     * @return array
     */
    public function afterGetMethodsDataProvider(): array
    {
        $tokenUiComponentInterface = $this->getMockBuilder(TokenUiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkmoPaymentMethod = $this
            ->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();
        $brainTreePaymentMethod = $this
            ->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();
        $brainTreeCCVaultTPaymentMethod = $this
            ->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();

        $checkmoPaymentMethod->expects($this->any())->method('getCode')
            ->willReturn(self::PAYMENT_METHOD_CHECKMO);
        $brainTreePaymentMethod->expects($this->any())->method('getCode')
            ->willReturn(self::PAYMENT_METHOD_BRAINTREE);
        $brainTreeCCVaultTPaymentMethod->expects($this->any())->method('getCode')
            ->willReturn(self::PAYMENT_METHOD_BRAINTREE_CC_VAULT);

        $paymentMethods = [
            $checkmoPaymentMethod,
            $brainTreePaymentMethod,
            $brainTreeCCVaultTPaymentMethod,
        ];
        $expectedResult1 = [
            $checkmoPaymentMethod,
            $brainTreePaymentMethod,
            $brainTreeCCVaultTPaymentMethod
        ];
        $expectedResult2 = [
            $checkmoPaymentMethod,
            $brainTreePaymentMethod,
        ];

        return [
            [$paymentMethods, $expectedResult1, [$tokenUiComponentInterface]],
            [$paymentMethods, $expectedResult2, []],
        ];
    }
}

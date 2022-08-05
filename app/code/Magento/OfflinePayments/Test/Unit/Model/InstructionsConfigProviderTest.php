<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Model;

use Magento\Framework\Escaper;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\InstructionsConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstructionsConfigProviderTest extends TestCase
{
    /**
     * @var InstructionsConfigProvider
     */
    private $model;

    /**
     * @var AbstractMethod|MockObject
     */
    private $methodOneMock;

    /**
     * @var AbstractMethod|MockObject
     */
    private $methodTwoMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    protected function setUp(): void
    {
        $this->methodOneMock = $this->getMockBuilder(AbstractMethod::class)
            ->addMethods(['getInstructions'])
            ->onlyMethods(['isAvailable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->methodTwoMock = $this->getMockBuilder(AbstractMethod::class)
            ->addMethods(['getInstructions'])
            ->onlyMethods(['isAvailable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var PaymentHelper|MockObject $paymentHelperMock */
        $paymentHelperMock = $this->createMock(PaymentHelper::class);
        $paymentHelperMock->expects($this->exactly(2))
            ->method('getMethodInstance')
            ->willReturnMap([
                [Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE, $this->methodOneMock],
                [Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE, $this->methodTwoMock],
            ]);

        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->model = new InstructionsConfigProvider(
            $paymentHelperMock,
            $this->escaperMock
        );
    }

    /**
     * @param bool $isOneAvailable
     * @param string $instructionsOne
     * @param bool $isTwoAvailable
     * @param string $instructionsTwo
     * @param array $result
     * @dataProvider dataProviderGetConfig
     */
    public function testGetConfig($isOneAvailable, $instructionsOne, $isTwoAvailable, $instructionsTwo, $result)
    {
        $this->methodOneMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn($isOneAvailable);
        $this->methodOneMock->expects($this->any())
            ->method('getInstructions')
            ->willReturn($instructionsOne);

        $this->methodTwoMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn($isTwoAvailable);
        $this->methodTwoMock->expects($this->any())
            ->method('getInstructions')
            ->willReturn($instructionsTwo);

        $this->assertEquals($result, $this->model->getConfig());
    }

    /**
     * @return array
     */
    public function dataProviderGetConfig()
    {
        $oneCode = Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE;
        $twoCode = Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;
        return [
            [false, '', false, '', []],
            [false, 'one', false, 'two', []],
            [true, '', false, '', ['payment' => ['instructions' => [$oneCode => '']]]],
            [true, 'text one', false, '', ['payment' => ['instructions' => [$oneCode => 'text one']]]],
            [false, '', true, '', ['payment' => ['instructions' => [$twoCode => '']]]],
            [false, '', true, 'text two', ['payment' => ['instructions' => [$twoCode => 'text two']]]],
            [true, '', true, '', ['payment' => ['instructions' => [$oneCode => '', $twoCode => '']]]],
            [
                true,
                'text one',
                true,
                'text two',
                ['payment' => ['instructions' => [$oneCode => 'text one', $twoCode => 'text two']]]
            ],
            [
                true,
                "\n",
                true,
                "\n",
                ['payment' => ['instructions' => [$oneCode => "<br />\n", $twoCode => "<br />\n"]]]
            ],
        ];
    }
}

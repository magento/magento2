<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

use Magento\Framework\Escaper;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\InstructionsConfigProvider;
use Magento\Payment\Model\Method\AbstractMethod;

class InstructionsConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var InstructionsConfigProvider */
    protected $model;

    /** @var AbstractMethod|\PHPUnit_Framework_MockObject_MockObject */
    protected $methodOneMock;

    /** @var AbstractMethod|\PHPUnit_Framework_MockObject_MockObject */
    protected $methodTwoMock;

    /** @var Escaper|\PHPUnit_Framework_MockObject_MockObject */
    protected $escaperMock;

    protected function setUp()
    {
        $this->methodOneMock = $this->createPartialMock(
            \Magento\Payment\Model\Method\AbstractMethod::class,
            ['isAvailable', 'getInstructions']
        );
        $this->methodTwoMock = $this->createPartialMock(
            \Magento\Payment\Model\Method\AbstractMethod::class,
            ['isAvailable', 'getInstructions']
        );

        $paymentHelperMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $paymentHelperMock->expects($this->exactly(2))
            ->method('getMethodInstance')
            ->willReturnMap([
                [Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE, $this->methodOneMock],
                [Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE, $this->methodTwoMock],
            ]);

        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
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

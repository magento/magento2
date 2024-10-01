<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Model;

use Magento\Framework\Escaper;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Model\CheckmoConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckmoConfigProviderTest extends TestCase
{
    /**
     * @var CheckmoConfigProvider
     */
    private $model;

    /**
     * @var Checkmo|MockObject
     */
    private $methodMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    protected function setUp(): void
    {
        $this->methodMock = $this->createMock(Checkmo::class);

        /** @var PaymentHelper|MockObject $paymentHelperMock */
        $paymentHelperMock = $this->createMock(PaymentHelper::class);
        $paymentHelperMock->expects($this->once())
            ->method('getMethodInstance')
            ->with(Checkmo::PAYMENT_METHOD_CHECKMO_CODE)
            ->willReturn($this->methodMock);

        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->model = new CheckmoConfigProvider(
            $paymentHelperMock,
            $this->escaperMock
        );
    }

    /**
     * @param bool $isAvailable
     * @param string $mailingAddress
     * @param string $payableTo
     * @param array $result
     * @dataProvider dataProviderGetConfig
     */
    public function testGetConfig($isAvailable, $mailingAddress, $payableTo, $result)
    {
        $this->methodMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn($isAvailable);
        $this->methodMock->expects($this->any())
            ->method('getMailingAddress')
            ->willReturn($mailingAddress);
        $this->methodMock->expects($this->any())
            ->method('getPayableTo')
            ->willReturn($payableTo);

        $this->assertEquals($result, $this->model->getConfig());
    }

    /**
     * @return array
     */
    public static function dataProviderGetConfig()
    {
        $checkmoCode = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;
        return [
            [false, '', '', []],
            [true, '', '', ['payment' => [$checkmoCode => ['mailingAddress' => '', 'payableTo' => '']]]],
            [true, 'address', '', ['payment' => [$checkmoCode => ['mailingAddress' => 'address', 'payableTo' => '']]]],
            [true, '', 'to', ['payment' => [$checkmoCode => ['mailingAddress' => '', 'payableTo' => 'to']]]],
            [true, 'addr', 'to', ['payment' => [$checkmoCode => ['mailingAddress' => 'addr', 'payableTo' => 'to']]]],
            [false, 'addr', 'to', []],
        ];
    }
}

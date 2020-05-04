<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Block\Checkout\Payment\Info;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @var Info
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $multiShippingMock;

    protected function setUp(): void
    {
        $this->multiShippingMock =
            $this->createMock(Multishipping::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Info::class,
            [
                'multishipping' => $this->multiShippingMock,
            ]
        );
    }

    public function testGetPaymentInfo()
    {
        $quoteMock = $this->createMock(Quote::class);
        $paymentInfoMock = $this->createMock(\Magento\Payment\Model\Info::class);
        $this->multiShippingMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentInfoMock);

        $this->model->getPaymentInfo();
    }
}

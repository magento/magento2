<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow;

use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflow\AvsEmsCodeMapper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class AvsEmsCodeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AvsEmsCodeMapper
     */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->mapper = new AvsEmsCodeMapper();
    }

    /**
     * Checks different variations for AVS codes mapping.
     *
     * @covers \Magento\Paypal\Model\Payflow\AvsEmsCodeMapper::getCode
     * @param string $avsZip
     * @param string $avsStreet
     * @param string $expected
     * @dataProvider getCodeDataProvider
     */
    public function testGetCode($avsZip, $avsStreet, $expected)
    {
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderPayment->expects(self::once())
            ->method('getMethod')
            ->willReturn(Config::METHOD_PAYFLOWPRO);

        $orderPayment->expects(self::once())
            ->method('getAdditionalInformation')
            ->willReturn([
                Info::PAYPAL_AVSZIP => $avsZip,
                Info::PAYPAL_AVSADDR => $avsStreet
            ]);

        self::assertEquals($expected, $this->mapper->getCode($orderPayment));
    }

    /**
     * Checks a test case, when payment order is not Payflow payment method.
     *
     * @covers \Magento\Paypal\Model\Payflow\AvsEmsCodeMapper::getCode
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "some_payment" does not supported by Payflow AVS mapper.
     */
    public function testGetCodeWithException()
    {
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderPayment->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn('some_payment');

        $this->mapper->getCode($orderPayment);
    }

    /**
     * Gets list of AVS codes.
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return [
            ['avsZip' => null, 'avsStreet' => null, 'expected' => ''],
            ['avsZip' => null, 'avsStreet' => 'Y', 'expected' => ''],
            ['avsZip' => 'Y', 'avsStreet' => null, 'expected' => ''],
            ['avsZip' => 'Y', 'avsStreet' => 'Y', 'expected' => 'Y'],
            ['avsZip' => 'N', 'avsStreet' => 'Y', 'expected' => 'A'],
            ['avsZip' => 'Y', 'avsStreet' => 'N', 'expected' => 'Z'],
            ['avsZip' => 'N', 'avsStreet' => 'N', 'expected' => 'N'],
            ['avsZip' => 'X', 'avsStreet' => 'Y', 'expected' => ''],
            ['avsZip' => 'N', 'avsStreet' => 'X', 'expected' => ''],
            ['avsZip' => '', 'avsStreet' => 'Y', 'expected' => ''],
            ['avsZip' => 'N', 'avsStreet' => '', 'expected' => '']
        ];
    }
}

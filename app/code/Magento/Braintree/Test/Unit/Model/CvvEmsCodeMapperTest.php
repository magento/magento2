<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Model\CvvEmsCodeMapper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CvvEmsCodeMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CvvEmsCodeMapper
     */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->mapper = new CvvEmsCodeMapper();
    }

    /**
     * Checks different variations for cvv codes mapping.
     *
     * @covers \Magento\Braintree\Model\CvvEmsCodeMapper::getCode
     * @param string $cvvCode
     * @param string $expected
     * @dataProvider getCodeDataProvider
     */
    public function testGetCode($cvvCode, $expected)
    {
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderPayment->expects(self::once())
            ->method('getAdditionalInformation')
            ->willReturn(['cvvResponseCode' => $cvvCode]);

        self::assertEquals($expected, $this->mapper->getCode($orderPayment));
    }

    /**
     * Gets variations of cvv codes and expected mapping result.
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return [
            ['cvvCode' => '', 'expected' => 'P'],
            ['cvvCode' => null, 'expected' => 'P'],
            ['cvvCode' => 'Unknown', 'expected' => 'P'],
            ['cvvCode' => 'M', 'expected' => 'M'],
            ['cvvCode' => 'N', 'expected' => 'N'],
            ['cvvCode' => 'U', 'expected' => 'P'],
            ['cvvCode' => 'I', 'expected' => 'P'],
            ['cvvCode' => 'S', 'expected' => 'S'],
            ['cvvCode' => 'A', 'expected' => ''],
        ];
    }
}

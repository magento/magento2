<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\CustomerDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Braintree\Gateway\Helper\SubjectReader;

/**
 * Class CustomerDataBuilderTest
 */
class CustomerDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var CustomerDataBuilder
     */
    private $builder;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new CustomerDataBuilder($this->subjectReaderMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [
            'payment' => null,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        $this->builder->build($buildSubject);
    }

    /**
     * @param array $billingData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild($billingData, $expectedResult)
    {
        $billingMock = $this->getBillingMock($billingData);

        $this->paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($billingMock);

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);

        self::assertEquals($expectedResult, $this->builder->build($buildSubject));
    }

    /**
     * @return array
     */
    public function dataProviderBuild()
    {
        return [
            [
                [
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                    'company' => 'Magento',
                    'phone' => '555-555-555',
                    'email' => 'john@magento.com'
                ],
                [
                    CustomerDataBuilder::CUSTOMER => [
                        CustomerDataBuilder::FIRST_NAME => 'John',
                        CustomerDataBuilder::LAST_NAME => 'Smith',
                        CustomerDataBuilder::COMPANY => 'Magento',
                        CustomerDataBuilder::PHONE => '555-555-555',
                        CustomerDataBuilder::EMAIL => 'john@magento.com',
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $billingData
     * @return AddressAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getBillingMock($billingData)
    {
        $addressMock = $this->createMock(AddressAdapterInterface::class);

        $addressMock->expects(static::once())
            ->method('getFirstname')
            ->willReturn($billingData['first_name']);
        $addressMock->expects(static::once())
            ->method('getLastname')
            ->willReturn($billingData['last_name']);
        $addressMock->expects(static::once())
            ->method('getCompany')
            ->willReturn($billingData['company']);
        $addressMock->expects(static::once())
            ->method('getTelephone')
            ->willReturn($billingData['phone']);
        $addressMock->expects(static::once())
            ->method('getEmail')
            ->willReturn($billingData['email']);

        return $addressMock;
    }
}

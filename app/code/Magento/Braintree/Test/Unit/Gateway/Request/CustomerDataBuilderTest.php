<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\CustomerDataBuilder;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CustomerDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    /**
     * @var CustomerDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->order = $this->createMock(OrderAdapterInterface::class);

        $this->builder = new CustomerDataBuilder(new SubjectReader());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [
            'payment' => null,
        ];

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

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);
        $this->order->method('getBillingAddress')
            ->willReturn($billingMock);

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

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
     * @return AddressAdapterInterface|MockObject
     */
    private function getBillingMock($billingData)
    {
        $address = $this->createMock(AddressAdapterInterface::class);

        $address->method('getFirstname')
            ->willReturn($billingData['first_name']);
        $address->method('getLastname')
            ->willReturn($billingData['last_name']);
        $address->method('getCompany')
            ->willReturn($billingData['company']);
        $address->method('getTelephone')
            ->willReturn($billingData['phone']);
        $address->method('getEmail')
            ->willReturn($billingData['email']);

        return $address;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Request\AddressDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

class AddressDataBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @var AddressDataBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->paymentDOMock = $this->getMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->getMock(OrderAdapterInterface::class);

        $this->builder = new AddressDataBuilder();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [
            'payment' => null,
        ];

        $this->builder->build($buildSubject);
    }

    public function testBuildNoAddresses()
    {
        $this->paymentDOMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn(null);

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        static::assertEmpty($this->builder->build($buildSubject));
    }

    /**
     * @param array $addressData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild($addressData, $expectedResult)
    {
        $addressMock = $this->getAddressMock($addressData);

        $this->paymentDOMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($addressMock);
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($addressMock);

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->assertEquals($expectedResult, $this->builder->build($buildSubject));
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
                    'street_1' => 'street1',
                    'street_2' => 'street2',
                    'city' => 'Chicago',
                    'region_code' => 'IL',
                    'country_id' => 'US',
                    'post_code' => '00000'
                ],
                [
                    AddressDataBuilder::SHIPPING_ADDRESS => [
                        AddressDataBuilder::FIRST_NAME => 'John',
                        AddressDataBuilder::LAST_NAME => 'Smith',
                        AddressDataBuilder::COMPANY => 'Magento',
                        AddressDataBuilder::STREET_ADDRESS => 'street1',
                        AddressDataBuilder::EXTENDED_ADDRESS => 'street2',
                        AddressDataBuilder::LOCALITY => 'Chicago',
                        AddressDataBuilder::REGION => 'IL',
                        AddressDataBuilder::POSTAL_CODE => '00000',
                        AddressDataBuilder::COUNTRY_CODE => 'US'

                    ],
                    AddressDataBuilder::BILLING_ADDRESS => [
                        AddressDataBuilder::FIRST_NAME => 'John',
                        AddressDataBuilder::LAST_NAME => 'Smith',
                        AddressDataBuilder::COMPANY => 'Magento',
                        AddressDataBuilder::STREET_ADDRESS => 'street1',
                        AddressDataBuilder::EXTENDED_ADDRESS => 'street2',
                        AddressDataBuilder::LOCALITY => 'Chicago',
                        AddressDataBuilder::REGION => 'IL',
                        AddressDataBuilder::POSTAL_CODE => '00000',
                        AddressDataBuilder::COUNTRY_CODE => 'US'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $addressData
     * @return AddressAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAddressMock($addressData)
    {
        $addressMock = $this->getMock(AddressAdapterInterface::class);

        $addressMock->expects(static::exactly(2))
            ->method('getFirstname')
            ->willReturn($addressData['first_name']);
        $addressMock->expects(static::exactly(2))
            ->method('getLastname')
            ->willReturn($addressData['last_name']);
        $addressMock->expects(static::exactly(2))
            ->method('getCompany')
            ->willReturn($addressData['company']);
        $addressMock->expects(static::exactly(2))
            ->method('getStreetLine1')
            ->willReturn($addressData['street_1']);
        $addressMock->expects(static::exactly(2))
            ->method('getStreetLine2')
            ->willReturn($addressData['street_2']);
        $addressMock->expects(static::exactly(2))
            ->method('getCity')
            ->willReturn($addressData['city']);
        $addressMock->expects(static::exactly(2))
            ->method('getRegionCode')
            ->willReturn($addressData['region_code']);
        $addressMock->expects(static::exactly(2))
            ->method('getPostcode')
            ->willReturn($addressData['post_code']);
        $addressMock->expects(static::exactly(2))
            ->method('getCountryId')
            ->willReturn($addressData['country_id']);

        return $addressMock;
    }
}

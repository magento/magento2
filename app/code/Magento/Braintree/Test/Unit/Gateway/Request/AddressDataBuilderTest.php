<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\AddressDataBuilder;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AddressDataBuilderTest
 */
class AddressDataBuilderTest extends \PHPUnit\Framework\TestCase
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
     * @var AddressDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->order = $this->createMock(OrderAdapterInterface::class);

        $this->builder = new AddressDataBuilder(new SubjectReader());
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

    public function testBuildNoAddresses()
    {
        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getShippingAddress')
            ->willReturn(null);
        $this->order->method('getBillingAddress')
            ->willReturn(null);

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        self::assertEquals([], $this->builder->build($buildSubject));
    }

    /**
     * @param array $addressData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild($addressData, $expectedResult)
    {
        $address = $this->getAddressMock($addressData);

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getShippingAddress')
            ->willReturn($address);
        $this->order->method('getBillingAddress')
            ->willReturn($address);

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
     * @return AddressAdapterInterface|MockObject
     */
    private function getAddressMock($addressData)
    {
        $addressMock = $this->createMock(AddressAdapterInterface::class);

        $addressMock->expects(self::exactly(2))
            ->method('getFirstname')
            ->willReturn($addressData['first_name']);
        $addressMock->expects(self::exactly(2))
            ->method('getLastname')
            ->willReturn($addressData['last_name']);
        $addressMock->expects(self::exactly(2))
            ->method('getCompany')
            ->willReturn($addressData['company']);
        $addressMock->expects(self::exactly(2))
            ->method('getStreetLine1')
            ->willReturn($addressData['street_1']);
        $addressMock->expects(self::exactly(2))
            ->method('getStreetLine2')
            ->willReturn($addressData['street_2']);
        $addressMock->expects(self::exactly(2))
            ->method('getCity')
            ->willReturn($addressData['city']);
        $addressMock->expects(self::exactly(2))
            ->method('getRegionCode')
            ->willReturn($addressData['region_code']);
        $addressMock->expects(self::exactly(2))
            ->method('getPostcode')
            ->willReturn($addressData['post_code']);
        $addressMock->expects(self::exactly(2))
            ->method('getCountryId')
            ->willReturn($addressData['country_id']);

        return $addressMock;
    }
}

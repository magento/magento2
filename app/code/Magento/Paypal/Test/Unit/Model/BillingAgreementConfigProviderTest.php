<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Paypal\Model\BillingAgreementConfigProvider;
use Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement;

class BillingAgreementConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentCustomerMock;

    /**
     * @var AgreementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementFactoryMock;

    protected function setUp()
    {
        $this->currentCustomerMock = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->setMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->agreementFactoryMock = $this->getMockBuilder(\Magento\Paypal\Model\Billing\AgreementFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new BillingAgreementConfigProvider(
            $this->currentCustomerMock,
            $this->agreementFactoryMock
        );
    }

    public function testGetConfig()
    {
        $customerId = 1;
        $agreements = [
            new \Magento\Framework\DataObject(['id' => 1, 'reference_id' => 'DFG123ER']),
            new \Magento\Framework\DataObject(['id' => 2, 'reference_id' => 'JKT153ER']),
        ];

        $expected = [
            'payment' => [
                'paypalBillingAgreement' => [
                    'agreements' => [
                        ['id' => 1, 'referenceId' => 'DFG123ER'],
                        ['id' => 2, 'referenceId' => 'JKT153ER']
                    ],
                    'transportName' => AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID
                ]
            ]
        ];

        $this->currentCustomerMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);

        $agreementMock = $this->getMockBuilder(\Magento\Paypal\Model\Billing\Agreement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $agreementMock->expects($this->once())
            ->method('getAvailableCustomerBillingAgreements')
            ->with($customerId)
            ->willReturn($agreements);

        $this->agreementFactoryMock->expects($this->once())->method('create')->willReturn($agreementMock);

        $this->assertEquals($expected, $this->configProvider->getConfig());
    }

    public function testGetConfigWithEmptyCustomer()
    {
        $customerId = 0;
        $expected = [
            'payment' => [
                'paypalBillingAgreement' => [
                    'agreements'=> [],
                    'transportName' => AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID
                ]
            ]
        ];
        $this->currentCustomerMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->agreementFactoryMock->expects($this->never())->method('create');
        $this->assertEquals($expected, $this->configProvider->getConfig());
    }
}

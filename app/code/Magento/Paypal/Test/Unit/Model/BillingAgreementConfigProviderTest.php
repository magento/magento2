<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\DataObject;
use Magento\Paypal\Model\Billing\Agreement;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Paypal\Model\BillingAgreementConfigProvider;
use Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BillingAgreementConfigProviderTest extends TestCase
{
    /**
     * @var CurrentCustomer|MockObject
     */
    protected $currentCustomerMock;

    /**
     * @var AgreementFactory|MockObject
     */
    protected $agreementFactoryMock;
    /**
     * @var BillingAgreementConfigProvider
     */
    private $configProvider;

    protected function setUp(): void
    {
        $this->currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->onlyMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->agreementFactoryMock = $this->getMockBuilder(AgreementFactory::class)
            ->onlyMethods(['create'])
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
            new DataObject(['id' => 1, 'reference_id' => 'DFG123ER']),
            new DataObject(['id' => 2, 'reference_id' => 'JKT153ER']),
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

        $agreementMock = $this->getMockBuilder(Agreement::class)
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

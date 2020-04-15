<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\CustomerData;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Paypal\CustomerData\BillingAgreement;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Escaper;

class BillingAgreementTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var CurrentCustomer | \PHPUnit\Framework\MockObject\MockObject
     */
    private $currentCustomer;

    /**
     * @var Data | \PHPUnit\Framework\MockObject\MockObject
     */
    private $paypalData;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paypalConfig;

    /**
     * @var BillingAgreement
     */
    private $billingAgreement;

    /**
     * @var Escaper
     */
    private $escaperMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->paypalConfig = $this->createMock(Config::class);
        $this->escaperMock = $helper->getObject(Escaper::class);
        $this->paypalConfig
            ->expects($this->once())
            ->method('setMethod')
            ->willReturnSelf();

        $this->paypalConfig->expects($this->once())
            ->method('setMethod')
            ->with(Config::METHOD_EXPRESS);

        $paypalConfigFactory = $this->createPartialMock(ConfigFactory::class, ['create']);
        $paypalConfigFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->paypalConfig);

        $customerId = 20;
        $this->currentCustomer = $this->createMock(CurrentCustomer::class);
        $this->currentCustomer->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paypalData = $this->createMock(Data::class);
        $this->billingAgreement = $helper->getObject(
            BillingAgreement::class,
            [
                'paypalConfigFactory' => $paypalConfigFactory,
                'paypalData' => $this->paypalData,
                'currentCustomer' => $this->currentCustomer,
                'escaper' => $this->escaperMock
            ]
        );
    }

    public function testGetSectionData()
    {
        $this->paypalData->expects($this->once())
            ->method('shouldAskToCreateBillingAgreement')
            ->with($this->paypalConfig, $this->currentCustomer->getCustomerId())
            ->willReturn(true);

        $result = $this->billingAgreement->getSectionData();

        $this->assertArrayHasKey('askToCreate', $result);
        $this->assertArrayHasKey('confirmUrl', $result);
        $this->assertArrayHasKey('confirmMessage', $result);
        $this->assertEquals(
            'Would you like to sign a billing agreement to streamline further purchases with PayPal?',
            $result['confirmMessage']
        );
        $this->assertTrue($result['askToCreate']);
    }

    public function testGetSectionDataNotNeedToCreateBillingAgreement()
    {
        $this->paypalData->expects($this->once())
            ->method('shouldAskToCreateBillingAgreement')
            ->with($this->paypalConfig, $this->currentCustomer->getCustomerId())
            ->willReturn(false);

        $result = $this->billingAgreement->getSectionData();

        $this->assertEmpty($result);
    }
}

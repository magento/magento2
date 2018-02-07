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

class BillingAgreementTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CurrentCustomer | \PHPUnit_Framework_MockObject_MockObject
     */
    private $currentCustomer;

    /**
     * @var Data | \PHPUnit_Framework_MockObject_MockObject
     */
    private $paypalData;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paypalConfig;

    /**
     * @var BillingAgreement
     */
    private $billingAgreement;

    protected function setUp()
    {
        $this->paypalConfig = $this->getMock(Config::class, [], [], '', false);
        $this->paypalConfig
            ->expects($this->once())
            ->method('setMethod')
            ->will($this->returnSelf());

        $this->paypalConfig->expects($this->once())
            ->method('setMethod')
            ->with(Config::METHOD_EXPRESS);

        $paypalConfigFactory = $this->getMock(ConfigFactory::class, ['create'], [], '', false);
        $paypalConfigFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->paypalConfig));

        $customerId = 20;
        $this->currentCustomer = $this->getMock(CurrentCustomer::class, [], [], '', false);
        $this->currentCustomer->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paypalData = $this->getMock(Data::class, [], [], '', false);

        $helper = new ObjectManager($this);
        $this->billingAgreement = $helper->getObject(
            BillingAgreement::class,
            [
                'paypalConfigFactory' => $paypalConfigFactory,
                'paypalData' => $this->paypalData,
                'currentCustomer' => $this->currentCustomer
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

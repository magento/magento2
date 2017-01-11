<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payment\Method\Billing;


use Magento\Quote\Api\Data\PaymentInterface;

class AbstractAgreementTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Model\Method\Agreement */
    protected $_model;

    protected function setUp()
    {
        $config = $this->getMockBuilder(\Magento\Paypal\Model\Config::class)->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isMethodAvailable')->will($this->returnValue(true));
        $proMock = $this->getMockBuilder(\Magento\Paypal\Model\Pro::class)->disableOriginalConstructor()->getMock();
        $proMock->expects($this->any())->method('getConfig')->will($this->returnValue($config));
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Paypal\Model\Method\Agreement::class,
            ['data' => [$proMock]]
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testIsActive()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\ResourceModel\Quote\Collection::class
        )->getFirstItem();
        $this->assertTrue($this->_model->isAvailable($quote));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testAssignData()
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $collection */
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\ResourceModel\Quote\Collection::class
        );
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $collection->getFirstItem();

        /** @var \Magento\Payment\Model\Info $info */
        $info = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Payment\Model\Info::class
        )->setQuote(
            $quote
        );
        $this->_model->setData('info_instance', $info);
        $billingAgreement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection::class
        )->getFirstItem();
        $data = new \Magento\Framework\DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID => $billingAgreement->getId()
                ]
            ]
        );
        $this->_model->assignData($data);
        $this->assertEquals(
            'REF-ID-TEST-678',
            $info->getAdditionalInformation(AbstractAgreement::PAYMENT_INFO_REFERENCE_ID)
        );
    }
}

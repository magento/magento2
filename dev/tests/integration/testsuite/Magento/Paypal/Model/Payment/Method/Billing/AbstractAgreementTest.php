<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model\Payment\Method\Billing;

use Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement;

class AbstractAgreementTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Model\Method\Agreement */
    protected $_model;

    protected function setUp()
    {
        $config = $this->getMockBuilder('\Magento\Paypal\Model\Config')->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isMethodAvailable')->will($this->returnValue(true));
        $proMock = $this->getMockBuilder('Magento\Paypal\Model\Pro')->disableOriginalConstructor()->getMock();
        $proMock->expects($this->any())->method('getConfig')->will($this->returnValue($config));
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Method\Agreement',
            array('data' => array($proMock))
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testIsActive()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Resource\Quote\Collection'
        )->getFirstItem();
        $this->assertTrue($this->_model->isAvailable($quote));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testAssignData()
    {
        /** @var \Magento\Sales\Model\Resource\Quote\Collection $collection */
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Resource\Quote\Collection'
        );
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $collection->getFirstItem();

        /** @var \Magento\Payment\Model\Info $info */
        $info = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Payment\Model\Info'
        )->setQuote(
            $quote
        );
        $this->_model->setData('info_instance', $info);
        $billingAgreement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection'
        )->getFirstItem();
        $data = array(AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID => $billingAgreement->getId());
        $this->_model->assignData($data);
        $this->assertEquals(
            'REF-ID-TEST-678',
            $info->getAdditionalInformation(AbstractAgreement::PAYMENT_INFO_REFERENCE_ID)
        );
    }
}

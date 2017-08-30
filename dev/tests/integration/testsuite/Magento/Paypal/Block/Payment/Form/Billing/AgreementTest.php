<?php
/**
 * Test for \Magento\Paypal\Block\Payment\Form\Billing\Agreement
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payment\Form\Billing;

class AgreementTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Paypal\Block\Payment\Form\Billing\Agreement */
    protected $_block;

    protected function setUp()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\ResourceModel\Quote\Collection::class
        )->getFirstItem();
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects(
            $this->once()
        )->method(
            'getBlock'
        )->will(
            $this->returnValue(new \Magento\Framework\DataObject(['quote' => $quote]))
        );
        $layout->expects($this->once())->method('getParentName')->will($this->returnValue('billing_agreement_form'));

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Paypal\Block\Payment\Form\Billing\Agreement::class
        );
        $this->_block->setLayout($layout);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testGetBillingAgreements()
    {
        $billingAgreements = $this->_block->getBillingAgreements();
        $this->assertEquals(1, count($billingAgreements));
        $this->assertEquals('REF-ID-TEST-678', array_shift($billingAgreements));
    }
}

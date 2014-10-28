<?php
/**
 * Test for \Magento\Paypal\Block\Payment\Form\Billing\Agreement
 *
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
namespace Magento\Paypal\Block\Payment\Form\Billing;

class AgreementTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Block\Payment\Form\Billing\Agreement */
    protected $_block;

    protected function setUp()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Resource\Quote\Collection'
        )->getFirstItem();
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects(
            $this->once()
        )->method(
            'getBlock'
        )->will(
            $this->returnValue(new \Magento\Framework\Object(array('quote' => $quote)))
        );
        $layout->expects($this->once())->method('getParentName')->will($this->returnValue('billing_agreement_form'));

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Block\Payment\Form\Billing\Agreement'
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

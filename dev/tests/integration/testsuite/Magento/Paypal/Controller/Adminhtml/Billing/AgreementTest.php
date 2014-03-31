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
namespace Magento\Paypal\Controller\Adminhtml\Billing;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
 *
 * @magentoAppArea adminhtml
 */
class AgreementTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerGridAction()
    {
        /** @var $session \Magento\Backend\Model\Session */
        Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session');

        $this->dispatch('backend/paypal/billing_agreement/grid');
        $response = $this->getResponse();

        $this->assertSelectCount(
            'button[type="button"][title="Reset Filter"]',
            1,
            $response->getBody(),
            "Response for billing agreement grid doesn't contain 'Reset Filter' button"
        );

        $this->assertSelectCount(
            '[id="billing_agreements"]',
            1,
            $response->getBody(),
            "Response for billing agreement grid doesn't contain grid"
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerInfoTabs()
    {
        /** @var \Magento\Paypal\Model\Resource\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection'
        );
        $agreementId = $billingAgreementCollection->getFirstItem()->getId();
        $this->dispatch('backend/paypal/billing_agreement/view/agreement/' . $agreementId);

        $this->assertSelectCount(
            'a[name="billing_agreement_info"]',
            1,
            $this->getResponse()->getBody(),
            "Response for billing agreement info doesn't contain billing agreement info tab"
        );

        $this->assertSelectRegExp(
            'a',
            '/customer\@example.com/',
            1,
            $this->getResponse()->getBody(),
            "Response for billing agreement info doesn't contain Customer info"
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerGrid()
    {
        $this->dispatch('backend/paypal/billing_agreement/customergrid/id/1');
        $this->assertSelectCount(
            'td[class="col-reference_id"]',
            1,
            $this->getResponse()->getBody(),
            "Response for billing agreement orders doesn't contain billing agreement customers grid"
        );
        $this->assertSelectRegExp(
            'td',
            '/REF-ID-TEST-678/',
            1,
            $this->getResponse()->getBody(),
            "Response for billing agreement info doesn't contain reference ID"
        );
    }
}

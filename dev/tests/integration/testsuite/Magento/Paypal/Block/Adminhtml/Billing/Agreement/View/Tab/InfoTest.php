<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View\Tab;

class InfoTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerGridAction()
    {
        /** @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection'
        )->load();
        $agreementId = $billingAgreementCollection->getFirstItem()->getId();
        $this->dispatch('backend/paypal/billing_agreement/view/agreement/' . $agreementId);

        $this->assertSelectCount(
            'a[name="billing_agreement_info"]',
            1,
            $this->getResponse()->getBody(),
            'Response for billing agreement info doesn\'t contain billing agreement info tab'
        );

        $this->assertSelectRegExp(
            'a',
            '/customer\@example.com/',
            1,
            $this->getResponse()->getBody(),
            'Response for billing agreement info doesn\'t contain Customer info'
        );
    }
}

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
namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View\Tab;

class InfoTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerGridAction()
    {
        /** @var \Magento\Paypal\Model\Resource\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection'
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

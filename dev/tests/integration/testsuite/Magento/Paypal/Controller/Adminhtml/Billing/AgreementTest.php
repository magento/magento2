<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
 *
 * @magentoAppArea adminhtml
 */
class AgreementTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testCustomerGrid()
    {
        $this->dispatch('backend/paypal/billing_agreement/customergrid/id/1');
        $this->assertSelectCount(
            'th[class="col-reference_id"]',
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

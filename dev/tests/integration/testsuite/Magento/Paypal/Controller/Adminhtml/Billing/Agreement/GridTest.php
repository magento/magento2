<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class GridTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_Paypal::billing_agreement_actions_view';
        $this->uri = 'backend/paypal/billing_agreement/grid';
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testAclHasAccess()
    {
        /** @var $session \Magento\Backend\Model\Session */
        Bootstrap::getObjectManager()->create('Magento\Backend\Model\Session');

        parent::testAclHasAccess();

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
}

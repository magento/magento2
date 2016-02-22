<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\ResourceModel\Billing\Agreement;

use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testAddCustomerDetails()
    {
        /** @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection'
        );

        $billingAgreementCollection->addCustomerDetails();

        $this->assertEquals(1, $billingAgreementCollection->count(), "Invalid collection items quantity.");
        /** @var \Magento\Paypal\Model\Billing\Agreement $billingAgreement */
        $billingAgreement = $billingAgreementCollection->getFirstItem();

        $expectedData = [
            'customer_id' => 1,
            'method_code' => 'paypal_express',
            'reference_id' => 'REF-ID-TEST-678',
            'status' => 'active',
            'store_id' => 1,
            'agreement_label' => 'TEST',
            'customer_email' => 'customer@example.com',
            'customer_firstname' => 'John',
            'customer_lastname' => 'Smith',
        ];
        foreach ($expectedData as $field => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $billingAgreement->getData($field),
                "'{$field}' field value is invalid."
            );
        }
    }
}

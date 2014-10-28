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
namespace Magento\Paypal\Model\Resource\Billing\Agreement;

use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Paypal/_files/billing_agreement.php
     */
    public function testAddCustomerDetails()
    {
        /** @var \Magento\Paypal\Model\Resource\Billing\Agreement\Collection $billingAgreementCollection */
        $billingAgreementCollection = Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection'
        );

        $billingAgreementCollection->addCustomerDetails();

        $this->assertEquals(1, $billingAgreementCollection->count(), "Invalid collection items quantity.");
        /** @var \Magento\Paypal\Model\Billing\Agreement $billingAgreement */
        $billingAgreement = $billingAgreementCollection->getFirstItem();

        $expectedData = array(
            'customer_id' => 1,
            'method_code' => 'paypal_express',
            'reference_id' => 'REF-ID-TEST-678',
            'status' => 'active',
            'store_id' => 1,
            'agreement_label' => 'TEST',
            'customer_email' => 'customer@example.com',
            'customer_firstname' => 'Firstname',
            'customer_lastname' => 'Lastname'
        );
        foreach ($expectedData as $field => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $billingAgreement->getData($field),
                "'{$field}' field value is invalid."
            );
        }
    }
}

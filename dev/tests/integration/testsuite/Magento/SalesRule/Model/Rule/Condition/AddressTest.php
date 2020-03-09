<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\SalesRule\Model\Rule\Condition\Address.
 */
class AddressTest extends TestCase
{
    use ConditionHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Tests cart price rule validation.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoDataFixture Magento/SalesRule/_files/rules_payment_method.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_payment_saved.php
     */
    public function testValidateRule()
    {
        $quote = $this->getQuote('test_order_1_with_payment');
        $rule = $this->getSalesRule('50% Off on Checkmo Payment Method');

        $this->assertTrue(
            $rule->validate($quote->getBillingAddress()),
            'Cart price rule validation failed.'
        );
    }
}

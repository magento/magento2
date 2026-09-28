<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Helper;

use Magento\TestFramework\Helper\Bootstrap;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests if method executes without fatal error when some vault payment method is enabled.
     *
     * @magentoConfigFixture current_store payment/payflowpro/active 1
     * @magentoConfigFixture current_store payment/payflowpro_cc_vault/active 1
     */
    public function testGetBillingAgreementMethodsWithVaultEnabled()
    {
        /** @var Data $model */
        $model = Bootstrap::getObjectManager()->create(Data::class);

        $this->assertEmpty($model->getBillingAgreementMethods());
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

class HstsVerifier extends AbstractHeaderVerifier
{
    /**
     * @magentoAdminConfigFixture web/secure/enable_hsts 1
     * @magentoAdminConfigFixture web/secure/use_in_frontend 1
     * @magentoAdminConfigFixture web/secure/use_in_adminhtml 1
     */
    public function testHeaderPresent()
    {
        parent::verifyHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    /**
     * @magentoAdminConfigFixture web/secure/enable_hsts 0
     * @magentoAdminConfigFixture web/secure/use_in_frontend 1
     * @magentoAdminConfigFixture web/secure/use_in_adminhtml 1
     */
    public function testHeaderNotPresent()
    {
        parent::verifyHeaderNotPresent('Strict-Transport-Security');
    }
}

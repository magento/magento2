<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

class UpgradeInsecureTest extends AbstractHeaderTest
{
    /**
     * @magentoAdminConfigFixture web/secure/enable_upgrade_insecure 1
     * @magentoAdminConfigFixture web/secure/use_in_frontend 1
     * @magentoAdminConfigFixture web/secure/use_in_adminhtml 1
     */
    public function testHeaderPresent()
    {
        parent::verifyHeader('Content-Security-Policy', 'upgrade-insecure-requests');
    }

    /**
     * @magentoAdminConfigFixture web/secure/enable_upgrade_insecure 0
     * @magentoAdminConfigFixture web/secure/use_in_frontend 1
     * @magentoAdminConfigFixture web/secure/use_in_adminhtml 1
     */
    public function testHeaderNotPresent()
    {
        parent::verifyHeaderNotPresent('Content-Security-Policy');
    }
}

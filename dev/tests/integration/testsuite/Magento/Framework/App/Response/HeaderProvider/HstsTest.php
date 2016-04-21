<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

class HstsTest extends AbstractHeaderTestCase
{
    /**
     * @magentoAdminConfigFixture web/secure/enable_hsts 1
     * @magentoAdminConfigFixture web/secure/use_in_frontend 1
     * @magentoAdminConfigFixture web/secure/use_in_adminhtml 1
     */
    public function testHeaderPresent()
    {
        $this->assertHeaderPresent('Strict-Transport-Security', 'max-age=31536000');
    }

    /**
     * @magentoAdminConfigFixture web/secure/enable_hsts 0
     * @magentoAdminConfigFixture web/secure/use_in_frontend 1
     * @magentoAdminConfigFixture web/secure/use_in_adminhtml 1
     */
    public function testHeaderNotPresent()
    {
        $this->assertHeaderNotPresent('Strict-Transport-Security');
    }
}

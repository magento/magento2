<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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

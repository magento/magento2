<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Integration\Block\Adminhtml\System\Config;

/**
 * Testing Oauth section in configs.
 *
 * @magentoAppArea adminhtml
 */
class OauthSectionTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Checks that OAuth Section in the system config is loaded
     */
    public function testOAuthSection()
    {
        $this->dispatch('backend/admin/system_config/edit/section/oauth/');
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('id="oauth_access_token_lifetime-head"', $body);
        $this->assertStringContainsString('id="oauth_cleanup-head"', $body);
        $this->assertStringContainsString('id="oauth_consumer-head"', $body);
        $this->assertStringContainsString('id="oauth_authentication_lock-head"', $body);
    }
}

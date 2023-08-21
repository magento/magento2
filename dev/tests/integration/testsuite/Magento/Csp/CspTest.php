<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test CSP being rendered when Magento processes an HTTP request.
 */
class CspTest extends AbstractController
{
    /**
     * Search the whole response for a string.
     *
     * @param \Magento\Framework\App\ResponseInterface|\Magento\Framework\App\Response\Http $response
     * @param string $search
     * @return bool
     */
    private function searchInResponse($response, string $search): bool
    {
        foreach ($response->getHeaders() as $header) {
            if (mb_stripos(mb_strtolower($header->toString()), mb_strtolower($search)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check that configured policies are rendered on frontend.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/policy_id default-src
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/inline 1
     * @magentoConfigFixture default_store csp/policies/admin/font_src/policy_id font-src
     * @magentoConfigFixture default_store csp/policies/admin/font_src/none 0
     * @magentoConfigFixture default_store csp/policies/admin/font_src/self 1
     * @return void
     */
    public function testStorefrontPolicies(): void
    {
        $this->dispatch('/');
        $response = $this->getResponse();

        $this->assertTrue($this->searchInResponse($response, 'Content-Security-Policy'));
        $this->assertTrue($this->searchInResponse($response, 'default-src'));
        $this->assertTrue($this->searchInResponse($response, 'http://magento.com'));
        $this->assertTrue($this->searchInResponse($response, 'http://devdocs.magento.com'));
        $this->assertTrue($this->searchInResponse($response, '\'self\''));
        $this->assertFalse($this->searchInResponse($response, '\'none\''));
        $this->assertTrue($this->searchInResponse($response, 'script-src'));
        $this->assertTrue($this->searchInResponse($response, '\'unsafe-inline\''));
        $this->assertTrue($this->searchInResponse($response, 'font-src'));
        //Policies configured in cps_whitelist.xml files
        $this->assertTrue($this->searchInResponse($response, 'object-src'));
        $this->assertTrue($this->searchInResponse($response, 'media-src'));
    }

    /**
     * Check that configured policies are rendered on backend.
     *
     * @magentoAppArea adminhtml
     * @magentoConfigFixture default_store csp/policies/admin/default_src/policy_id default-src
     * @magentoConfigFixture default_store csp/policies/admin/default_src/none 0
     * @magentoConfigFixture default_store csp/policies/admin/default_src/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/admin/default_src/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/admin/default_src/self 1
     * @magentoConfigFixture default_store csp/policies/admin/script_src/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/admin/script_src/none 0
     * @magentoConfigFixture default_store csp/policies/admin/default_src/self 1
     * @magentoConfigFixture default_store csp/policies/admin/default_src/inline 1
     * @magentoConfigFixture default_store csp/policies/storefront/font_src/policy_id font-src
     * @magentoConfigFixture default_store csp/policies/storefront/font_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/font_src/self 1
     * @return void
     */
    public function testAdminPolicies(): void
    {
        $this->dispatch('backend/');
        $response = $this->getResponse();

        $this->assertTrue($this->searchInResponse($response, 'Content-Security-Policy'));
        $this->assertTrue($this->searchInResponse($response, 'default-src'));
        $this->assertTrue($this->searchInResponse($response, 'http://magento.com'));
        $this->assertTrue($this->searchInResponse($response, 'http://devdocs.magento.com'));
        $this->assertTrue($this->searchInResponse($response, '\'self\''));
        $this->assertFalse($this->searchInResponse($response, '\'none\''));
        $this->assertTrue($this->searchInResponse($response, 'script-src'));
        $this->assertTrue($this->searchInResponse($response, '\'unsafe-inline\''));
        $this->assertTrue($this->searchInResponse($response, 'font-src'));
    }

    /**
     * Check that CSP mode is considered when rendering policies.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/policy_id default-src
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/self 1
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 1
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri /cspEndpoint/
     * @magentoConfigFixture default_store csp/mode/admin/report_only 0
     * @return void
     */
    public function testReportOnlyMode(): void
    {
        $this->dispatch('/');
        $response = $this->getResponse();

        $this->assertTrue($this->searchInResponse($response, 'Content-Security-Policy-Report-Only'));
        $this->assertTrue($this->searchInResponse($response, '/cspEndpoint/'));
        $this->assertTrue($this->searchInResponse($response, 'default-src'));
        $this->assertTrue($this->searchInResponse($response, 'http://magento.com'));
        $this->assertTrue($this->searchInResponse($response, 'http://devdocs.magento.com'));
        $this->assertTrue($this->searchInResponse($response, '\'self\''));
    }

    /**
     * Check that CSP reporting options are rendered for 'restrict' mode as well.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/policy_id default-src
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/self 1
     * @magentoConfigFixture default_store csp/mode/storefront/report_only 0
     * @magentoConfigFixture default_store csp/mode/storefront/report_uri /cspEndpoint/
     * @magentoConfigFixture default_store csp/mode/admin/report_only 0
     * @return void
     */
    public function testRestrictMode(): void
    {
        $this->dispatch('/');
        $response = $this->getResponse();

        $this->assertFalse($this->searchInResponse($response, 'Content-Security-Policy-Report-Only'));
        $this->assertTrue($this->searchInResponse($response, 'Content-Security-Policy'));
        $this->assertTrue($this->searchInResponse($response, '/cspEndpoint/'));
        $this->assertTrue($this->searchInResponse($response, 'default-src'));
        $this->assertTrue($this->searchInResponse($response, 'http://magento.com'));
        $this->assertTrue($this->searchInResponse($response, 'http://devdocs.magento.com'));
        $this->assertTrue($this->searchInResponse($response, '\'self\''));
    }
}

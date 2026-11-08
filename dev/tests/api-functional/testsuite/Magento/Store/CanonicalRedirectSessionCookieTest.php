<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\HttpClient\CurlClientWithCookies;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests the session cookie on the actual canonical HTTP response.
 */
class CanonicalRedirectSessionCookieTest extends WebapiAbstract
{
    /**
     * @magentoConfigFixture default_store web/url/redirect_to_base 301
     * @magentoConfigFixture default_store web/unsecure/base_url https://canonical-redirect.example/
     * @magentoConfigFixture default_store web/unsecure/base_link_url https://canonical-redirect.example/
     * @magentoConfigFixture default_store web/secure/base_url https://canonical-redirect.example/
     * @magentoConfigFixture default_store web/secure/base_link_url https://canonical-redirect.example/
     *
     * @return void
     */
    public function testRedirectDoesNotSetOrRenewSessionCookie(): void
    {
        $client = Bootstrap::getObjectManager()->get(CurlClientWithCookies::class);
        $path = 'customer/account/login/';
        foreach ([[], ['Cookie: PHPSESSID=canonical-redirect-session']] as $headers) {
            $response = $client->get($path, [], $headers);
            self::assertSame(301, $response['meta']['http_code']);
            self::assertStringContainsString(
                'Location: https://canonical-redirect.example/' . $path,
                $response['header']
            );
            self::assertNotContains('PHPSESSID', array_column($response['cookies'], 'name'));
        }
    }
}

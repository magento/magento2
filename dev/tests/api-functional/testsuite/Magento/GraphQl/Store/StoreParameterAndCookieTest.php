<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\HttpClient\CurlClient;

/**
 * Store scope taken from the ___store parameter and the store cookie when no Store header is sent
 */
class StoreParameterAndCookieTest extends GraphQlAbstract
{
    public function testMalformedStoreParameterFallsBackToDefaultStore(): void
    {
        $response = $this->query(['___store' => ['_data' => ['code' => 'default']]]);

        self::assertArrayNotHasKey('errors', $response);
        self::assertSame('default', $response['data']['storeConfig']['store_code']);
    }

    public function testAdminStoreParameterFallsBackToDefaultStore(): void
    {
        $response = $this->query(['___store' => 'admin']);

        self::assertArrayNotHasKey('errors', $response);
        self::assertSame('default', $response['data']['storeConfig']['store_code']);
    }

    public function testAdminStoreCookieFallsBackToDefaultStore(): void
    {
        $response = $this->query([], ['Cookie: store=admin']);

        self::assertArrayNotHasKey('errors', $response);
        self::assertSame('default', $response['data']['storeConfig']['store_code']);
    }

    public function testUnknownStoreCookieFallsBackToDefaultStore(): void
    {
        $response = $this->query([], ['Cookie: store=not_existing_store']);

        self::assertArrayNotHasKey('errors', $response);
        self::assertSame('default', $response['data']['storeConfig']['store_code']);
    }

    #[DataFixture(Store::class, as: 'store')]
    public function testStoreParameterSwitchesToStoreOfSameWebsite(): void
    {
        $storeCode = DataFixtureStorageManager::getStorage()->get('store')->getCode();

        $response = $this->query(['___store' => $storeCode]);

        self::assertArrayNotHasKey('errors', $response);
        self::assertSame($storeCode, $response['data']['storeConfig']['store_code']);
    }

    #[DataFixture(Store::class, as: 'store')]
    public function testStoreCookieSwitchesToStoreOfSameWebsite(): void
    {
        $storeCode = DataFixtureStorageManager::getStorage()->get('store')->getCode();

        $response = $this->query([], ['Cookie: store=' . $storeCode]);

        self::assertArrayNotHasKey('errors', $response);
        self::assertSame($storeCode, $response['data']['storeConfig']['store_code']);
    }

    /**
     * Send a GET request without a Store header; GraphQlAbstract has no way to add query string parameters.
     * The unique alias keeps a cached response of another test from being served.
     *
     * @param array $parameters
     * @param string[] $headers
     * @return array
     */
    private function query(array $parameters, array $headers = []): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $body = $objectManager->get(CurlClient::class)->get(
            rtrim(TESTS_BASE_URL, '/') . '/graphql',
            ['query' => '{storeConfig{store_code a' . uniqid() . ': id}}'] + $parameters,
            $headers
        );

        return $objectManager->get(SerializerInterface::class)->unserialize($body);
    }
}

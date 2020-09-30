<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GraphQl\Model\Cors\Configuration;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CorsHeadersTest extends GraphQlAbstract
{
    /**
     * @var Config $config
     */
    private $resourceConfig;
    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = ObjectManager::getInstance();

        $this->resourceConfig = $objectManager->get(Config::class);
        $this->reinitConfig = $objectManager->get(ReinitableConfigInterface::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_HEADERS_ENABLED, 0);
        $this->reinitConfig->reinit();
    }

    public function testNoCorsHeadersWhenCorsIsDisabled(): void
    {
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_HEADERS_ENABLED, 0);
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_HEADERS, 'Origin');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOW_CREDENTIALS, '1');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_METHODS, 'GET,POST');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_ORIGINS, 'magento.local');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_MAX_AGE, '86400');
        $this->reinitConfig->reinit();

        $headers = $this->getHeadersFromIntrospectionQuery();

        self::assertArrayNotHasKey('Access-Control-Max-Age', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Headers', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Methods', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $headers);
    }

    public function testCorsHeadersWhenCorsIsEnabled(): void
    {
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_HEADERS_ENABLED, 1);
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_HEADERS, 'Origin');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOW_CREDENTIALS, '1');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_METHODS, 'GET,POST');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_ORIGINS, 'http://magento.local');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_MAX_AGE, '86400');
        $this->reinitConfig->reinit();

        $headers = $this->getHeadersFromIntrospectionQuery();

        self::assertEquals('Origin', $headers['Access-Control-Allow-Headers']);
        self::assertEquals('1', $headers['Access-Control-Allow-Credentials']);
        self::assertEquals('GET,POST', $headers['Access-Control-Allow-Methods']);
        self::assertEquals('http://magento.local', $headers['Access-Control-Allow-Origin']);
        self::assertEquals('86400', $headers['Access-Control-Max-Age']);
    }

    public function testEmptyCorsHeadersWhenCorsIsEnabled(): void
    {
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_HEADERS_ENABLED, 1);
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_HEADERS, '');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOW_CREDENTIALS, '');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_METHODS, '');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_ALLOWED_ORIGINS, '');
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_CORS_MAX_AGE, '');
        $this->reinitConfig->reinit();

        $headers = $this->getHeadersFromIntrospectionQuery();

        self::assertArrayNotHasKey('Access-Control-Max-Age', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Credentials', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Headers', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Methods', $headers);
        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $headers);
    }

    private function getHeadersFromIntrospectionQuery(): array
    {
        $query
            = <<<QUERY
 query IntrospectionQuery {
    __schema {
        types {
        name
        }
    }
  }
QUERY;

        return $this->graphQlQueryWithResponseHeaders($query)['headers'] ?? [];
    }
}

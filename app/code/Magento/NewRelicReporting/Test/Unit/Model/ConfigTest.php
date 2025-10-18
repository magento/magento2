<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\NewRelicReporting\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test for Config model
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptorMock;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config|MockObject
     */
    private $resourceConfigMock;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->encryptorMock = $this->createMock(EncryptorInterface::class);
        $this->resourceConfigMock = $this->createMock(\Magento\Config\Model\ResourceModel\Config::class);

        $this->config = new Config($this->scopeConfigMock, $this->encryptorMock, $this->resourceConfigMock);
    }

    /**
     * Test isNewRelicEnabled when enabled
     */
    public function testIsNewRelicEnabledTrue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLED)
            ->willReturn(true);

        $this->assertTrue($this->config->isNewRelicEnabled());
    }

    /**
     * Test isNewRelicEnabled when disabled
     */
    public function testIsNewRelicEnabledFalse()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLED)
            ->willReturn(false);

        $this->assertFalse($this->config->isNewRelicEnabled());
    }

    /**
     * Test getNewRelicApiUrl
     */
    public function testGetNewRelicApiUrl()
    {
        $expectedUrl = 'https://api.newrelic.com/v2/applications/%s/deployments.json';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_URL)
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->config->getNewRelicApiUrl());
    }

    /**
     * Test getNewRelicApiKey with encrypted value
     */
    public function testGetNewRelicApiKey()
    {
        $encryptedKey = 'encrypted_api_key_value';
        $decryptedKey = 'NRAK-ABCD1234567890';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_KEY)
            ->willReturn($encryptedKey);

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($encryptedKey)
            ->willReturn($decryptedKey);

        $this->assertEquals($decryptedKey, $this->config->getNewRelicApiKey());
    }

    /**
     * Test getNewRelicAppId
     */
    public function testGetNewRelicAppId()
    {
        $expectedAppId = '123456789';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_ID)
            ->willReturn($expectedAppId);

        $this->assertEquals($expectedAppId, $this->config->getNewRelicAppId());
    }

    /**
     * Test getNewRelicAppName
     */
    public function testGetNewRelicAppName()
    {
        $expectedAppName = 'My Application';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_NAME)
            ->willReturn($expectedAppName);

        $this->assertEquals($expectedAppName, $this->config->getNewRelicAppName());
    }

    /**
     * Test getNerdGraphUrl
     */
    public function testGetNerdGraphUrl()
    {
        $expectedUrl = 'https://api.newrelic.com/graphql';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_NERD_GRAPH_API_URL)
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->config->getNerdGraphUrl());
    }

    /**
     * Test getEntityGuid
     */
    public function testGetEntityGuid()
    {
        $expectedGuid = 'ENTITY123456789';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_ENTITY_GUID)
            ->willReturn($expectedGuid);

        $this->assertEquals($expectedGuid, $this->config->getEntityGuid());
    }

    /**
     * Test getApiMode
     */
    public function testGetApiMode()
    {
        $expectedMode = 'nerdgraph';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_MODE)
            ->willReturn($expectedMode);

        $this->assertEquals($expectedMode, $this->config->getApiMode());
    }

    /**
     * Test isNerdGraphMode when mode is nerdgraph
     */
    public function testIsNerdGraphModeTrue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_MODE)
            ->willReturn('nerdgraph');

        $this->assertTrue($this->config->isNerdGraphMode());
    }

    /**
     * Test isNerdGraphMode when mode is v2_rest
     */
    public function testIsNerdGraphModeFalse()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_MODE)
            ->willReturn('v2_rest');

        $this->assertFalse($this->config->isNerdGraphMode());
    }

    /**
     * Test isNerdGraphMode when mode is empty
     */
    public function testIsNerdGraphModeEmptyMode()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_MODE)
            ->willReturn('');

        $this->assertFalse($this->config->isNerdGraphMode());
    }

    /**
     * Test getNerdGraphUrl with custom value
     */
    public function testGetNerdGraphUrlCustom()
    {
        $customUrl = 'https://api.eu.newrelic.com/graphql';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_NERD_GRAPH_API_URL)
            ->willReturn($customUrl);

        $this->assertEquals($customUrl, $this->config->getNerdGraphUrl());
    }

    /**
     * Test getEntityGuid with empty value
     */
    public function testGetEntityGuidEmpty()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_ENTITY_GUID)
            ->willReturn('');

        $this->assertEquals('', $this->config->getEntityGuid());
    }

    /**
     * Test getNewRelicApiKey with empty encrypted value
     */
    public function testGetNewRelicApiKeyEmpty()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_KEY)
            ->willReturn('');

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with('')
            ->willReturn('');

        $this->assertEquals('', $this->config->getNewRelicApiKey());
    }

    /**
     * Test getNewRelicAppId with integer return
     */
    public function testGetNewRelicAppIdInteger()
    {
        $appId = 123456789;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_ID)
            ->willReturn($appId);

        $this->assertEquals($appId, $this->config->getNewRelicAppId());
    }

    /**
     * Test type casting for getNewRelicAppId
     */
    public function testGetNewRelicAppIdTypeCasting()
    {
        $stringAppId = '987654321';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_ID)
            ->willReturn($stringAppId);

        $this->assertEquals($stringAppId, $this->config->getNewRelicAppId());
    }

    /**
     * Test getApiMode with null value
     */
    public function testGetApiModeNull()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_MODE)
            ->willReturn(null);

        $this->assertEquals('', $this->config->getApiMode());
    }

    /**
     * Test configuration path constants/values
     */
    public function testConfigurationPaths()
    {
        // Test multiple configuration calls to ensure consistent paths
        $this->scopeConfigMock->expects($this->exactly(8))
            ->method('getValue')
            ->willReturnCallback(function ($path) {
                $values = [
                    Config::XML_PATH_API_URL => 'api_url_value',
                    Config::XML_PATH_API_KEY => 'encrypted_api_key',
                    Config::XML_PATH_APP_ID => '123456789',
                    Config::XML_PATH_APP_NAME => 'app_name_value',
                    Config::XML_PATH_NERD_GRAPH_API_URL => 'nerdgraph_url_value',
                    Config::XML_PATH_ENTITY_GUID => 'entity_guid_value',
                    Config::XML_PATH_API_MODE => 'nerdgraph'
                ];
                return $values[$path] ?? null;
            });

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with('encrypted_api_key')
            ->willReturn('decrypted_api_key');

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLED)
            ->willReturn(true);

        // Test all methods
        $this->assertEquals('api_url_value', $this->config->getNewRelicApiUrl());
        $this->assertEquals('decrypted_api_key', $this->config->getNewRelicApiKey());
        $this->assertEquals(123456789, $this->config->getNewRelicAppId());
        $this->assertEquals('app_name_value', $this->config->getNewRelicAppName());
        $this->assertEquals('nerdgraph_url_value', $this->config->getNerdGraphUrl());
        $this->assertEquals('entity_guid_value', $this->config->getEntityGuid());
        $this->assertEquals('nerdgraph', $this->config->getApiMode());
        $this->assertTrue($this->config->isNerdGraphMode());
        $this->assertTrue($this->config->isNewRelicEnabled());
    }

    /**
     * Test encryption/decryption integration
     */
    public function testEncryptionDecryptionIntegration()
    {
        $plainTextKey = 'NRAK-PLAIN-TEXT-API-KEY';
        $encryptedKey = 'encrypted_string_abc123';

        // Test encryption flow (normally done by admin save)
        $this->encryptorMock->expects($this->once())
            ->method('encrypt')
            ->with($plainTextKey)
            ->willReturn($encryptedKey);

        $encryptedResult = $this->encryptorMock->encrypt($plainTextKey);
        $this->assertEquals($encryptedKey, $encryptedResult);

        // Test decryption flow (what Config does)
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_KEY)
            ->willReturn($encryptedKey);

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($encryptedKey)
            ->willReturn($plainTextKey);

        $decryptedResult = $this->config->getNewRelicApiKey();
        $this->assertEquals($plainTextKey, $decryptedResult);
    }

    /**
     * Test EU endpoint URL configuration
     */
    public function testGetNerdGraphUrlWithEuEndpoint(): void
    {
        $euNerdGraphUrl = 'https://api.eu.newrelic.com/graphql';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_NERD_GRAPH_API_URL)
            ->willReturn($euNerdGraphUrl);

        $result = $this->config->getNerdGraphUrl();
        $this->assertEquals($euNerdGraphUrl, $result);
    }

    /**
     * Test EU v2 REST API URL configuration
     */
    public function testGetNewRelicApiUrlWithEuEndpoint(): void
    {
        $euApiUrl = 'https://api.eu.newrelic.com/v2/applications/%s/deployments.json';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_API_URL)
            ->willReturn($euApiUrl);

        $result = $this->config->getNewRelicApiUrl();
        $this->assertEquals($euApiUrl, $result);
    }

    /**
     * Test EU Insights API URL configuration
     */
    public function testGetInsightsApiUrlWithEuEndpoint(): void
    {
        $euInsightsUrl = 'https://insights-collector.eu01.nr-data.net/v1/accounts/%s/events';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSIGHTS_API_URL)
            ->willReturn($euInsightsUrl);

        $result = $this->config->getInsightsApiUrl();
        $this->assertEquals($euInsightsUrl, $result);
    }

    /**
     * Test combined EU configuration setup
     */
    public function testCompleteEuEndpointConfiguration(): void
    {
        $euEndpoints = [
            Config::XML_PATH_NERD_GRAPH_API_URL => 'https://api.eu.newrelic.com/graphql',
            Config::XML_PATH_API_URL => 'https://api.eu.newrelic.com/v2/applications/%s/deployments.json',
            Config::XML_PATH_INSIGHTS_API_URL =>
                'https://insights-collector.eu01.nr-data.net/v1/accounts/%s/events',
            Config::XML_PATH_API_MODE => 'nerdgraph',
            Config::XML_PATH_ENABLED => '1'
        ];

        $this->scopeConfigMock->expects($this->exactly(5))
            ->method('getValue')
            ->willReturnCallback(function ($path) use ($euEndpoints) {
                return $euEndpoints[$path] ?? null;
            });

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLED)
            ->willReturn(true);

        // Test all EU endpoints are configured correctly
        $this->assertEquals('https://api.eu.newrelic.com/graphql', $this->config->getNerdGraphUrl());
        $this->assertEquals(
            'https://api.eu.newrelic.com/v2/applications/%s/deployments.json',
            $this->config->getNewRelicApiUrl()
        );
        $this->assertEquals(
            'https://insights-collector.eu01.nr-data.net/v1/accounts/%s/events',
            $this->config->getInsightsApiUrl()
        );
        $this->assertEquals('nerdgraph', $this->config->getApiMode());
        $this->assertTrue($this->config->isNewRelicEnabled());
        $this->assertTrue($this->config->isNerdGraphMode());
    }

    /**
     * Test getInsightsApiUrl
     */
    public function testGetInsightsApiUrl()
    {
        $expectedUrl = 'https://insights-collector.newrelic.com/v1/accounts/%s/events';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSIGHTS_API_URL)
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->config->getInsightsApiUrl());
    }

    /**
     * Test getInsightsApiUrl with null value returns empty string
     */
    public function testGetInsightsApiUrlNull()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSIGHTS_API_URL)
            ->willReturn(null);

        $this->assertEquals('', $this->config->getInsightsApiUrl());
    }

    /**
     * Test getNewRelicAccountId
     */
    public function testGetNewRelicAccountId()
    {
        $expectedAccountId = '2468135';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_ACCOUNT_ID)
            ->willReturn($expectedAccountId);

        $this->assertEquals($expectedAccountId, $this->config->getNewRelicAccountId());
    }

    /**
     * Test getNewRelicAccountId with null value returns empty string
     */
    public function testGetNewRelicAccountIdNull()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_ACCOUNT_ID)
            ->willReturn(null);

        $this->assertEquals('', $this->config->getNewRelicAccountId());
    }

    /**
     * Test getNewRelicAccountId with integer value gets cast to string
     */
    public function testGetNewRelicAccountIdIntegerCasting()
    {
        $intAccountId = 13579246;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_ACCOUNT_ID)
            ->willReturn($intAccountId);

        $this->assertEquals('13579246', $this->config->getNewRelicAccountId());
    }

    /**
     * Test getInsightsInsertKey
     */
    public function testGetInsightsInsertKey()
    {
        $encryptedKey = 'encrypted_insights_key';
        $decryptedKey = 'NRII-ABCD1234567890';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSIGHTS_INSERT_KEY)
            ->willReturn($encryptedKey);

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($encryptedKey)
            ->willReturn($decryptedKey);

        $this->assertEquals($decryptedKey, $this->config->getInsightsInsertKey());
    }

    /**
     * Test getInsightsInsertKey with empty encrypted value
     */
    public function testGetInsightsInsertKeyEmpty()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSIGHTS_INSERT_KEY)
            ->willReturn('');

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with('')
            ->willReturn('');

        $this->assertEquals('', $this->config->getInsightsInsertKey());
    }

    /**
     * Test getInsightsInsertKey with null value
     */
    public function testGetInsightsInsertKeyNull()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_INSIGHTS_INSERT_KEY)
            ->willReturn(null);

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with(null)
            ->willReturn('');

        $this->assertEquals('', $this->config->getInsightsInsertKey());
    }

    /**
     * Test isSeparateApps when enabled
     */
    public function testIsSeparateAppsTrue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SEPARATE_APPS)
            ->willReturn('1');

        $this->assertTrue($this->config->isSeparateApps());
    }

    /**
     * Test isSeparateApps when disabled
     */
    public function testIsSeparateAppsFalse()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SEPARATE_APPS)
            ->willReturn('0');

        $this->assertFalse($this->config->isSeparateApps());
    }

    /**
     * Test isSeparateApps with null value returns false
     */
    public function testIsSeparateAppsNull()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SEPARATE_APPS)
            ->willReturn(null);

        $this->assertFalse($this->config->isSeparateApps());
    }

    /**
     * Test isSeparateApps with boolean true
     */
    public function testIsSeparateAppsBooleanTrue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SEPARATE_APPS)
            ->willReturn(true);

        $this->assertTrue($this->config->isSeparateApps());
    }

    /**
     * Test isCronEnabled when enabled
     */
    public function testIsCronEnabledTrue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_CRON_ENABLED)
            ->willReturn(true);

        $this->assertTrue($this->config->isCronEnabled());
    }

    /**
     * Test isCronEnabled when disabled
     */
    public function testIsCronEnabledFalse()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_CRON_ENABLED)
            ->willReturn(false);

        $this->assertFalse($this->config->isCronEnabled());
    }

    /**
     * Test disableModule method
     */
    public function testDisableModule()
    {
        $this->resourceConfigMock->expects($this->once())
            ->method('saveConfig')
            ->with(Config::XML_PATH_ENABLED, 0, 'default', 0);

        $this->config->disableModule();
    }

    /**
     * Test disableModule with custom scope and scope ID
     * @throws ReflectionException
     */
    public function testDisableModuleCustomScope()
    {
        // We need to use reflection to test the protected setConfigValue method with custom parameters
        $reflection = new \ReflectionClass($this->config);
        $setConfigValueMethod = $reflection->getMethod('setConfigValue');

        $this->resourceConfigMock->expects($this->once())
            ->method('saveConfig')
            ->with('custom/path', 'custom_value', 'stores', 1);

        $setConfigValueMethod->invoke($this->config, 'custom/path', 'custom_value', 'stores', 1);
    }

    /**
     * Test all constants are defined and have expected values
     */
    public function testConstants()
    {
        // Test parameter constants
        $this->assertEquals('lineItemCount', Config::ORDER_ITEMS);
        $this->assertEquals('orderValue', Config::ORDER_VALUE);
        $this->assertEquals('Order', Config::ORDER_PLACED);
        $this->assertEquals('adminId', Config::ADMIN_USER_ID);
        $this->assertEquals('adminUser', Config::ADMIN_USER);
        $this->assertEquals('adminName', Config::ADMIN_NAME);
        $this->assertEquals('customerId', Config::CUSTOMER_ID);
        $this->assertEquals('CustomerName', Config::CUSTOMER_NAME);
        $this->assertEquals('CustomerCount', Config::CUSTOMER_COUNT);
        $this->assertEquals('systemCacheFlush', Config::FLUSH_CACHE);
        $this->assertEquals('store', Config::STORE);
        $this->assertEquals('StoreViewCount', Config::STORE_VIEW_COUNT);
        $this->assertEquals('website', Config::WEBSITE);
        $this->assertEquals('WebsiteCount', Config::WEBSITE_COUNT);
        $this->assertEquals('adminProductChange', Config::PRODUCT_CHANGE);
        $this->assertEquals('productCatalogSize', Config::PRODUCT_COUNT);
        $this->assertEquals('productCatalogConfigurableSize', Config::CONFIGURABLE_COUNT);
        $this->assertEquals('productCatalogActiveSize', Config::ACTIVE_COUNT);
        $this->assertEquals('productCatalogCategorySize', Config::CATEGORY_SIZE);
        $this->assertEquals('CatalogCategoryCount', Config::CATEGORY_COUNT);
        $this->assertEquals('enabledModuleCount', Config::ENABLED_MODULE_COUNT);
        $this->assertEquals('ModulesEnabled', Config::MODULES_ENABLED);
        $this->assertEquals('ModulesDisabled', Config::MODULES_DISABLED);
        $this->assertEquals('ModulesInstalled', Config::MODULES_INSTALLED);
        $this->assertEquals('moduleInstalled', Config::MODULE_INSTALLED);
        $this->assertEquals('moduleUninstalled', Config::MODULE_UNINSTALLED);
        $this->assertEquals('moduleEnabled', Config::MODULE_ENABLED);
        $this->assertEquals('moduleDisabled', Config::MODULE_DISABLED);

        // Test state flag constants
        $this->assertEquals('installed', Config::INSTALLED);
        $this->assertEquals('uninstalled', Config::UNINSTALLED);
        $this->assertEquals('enabled', Config::ENABLED);
        $this->assertEquals('disabled', Config::DISABLED);
        $this->assertEquals('true', Config::TRUE);
        $this->assertEquals('false', Config::FALSE);
    }

    /**
     * Test type casting for getNewRelicAppId with string zero
     */
    public function testGetNewRelicAppIdZeroString()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_ID)
            ->willReturn('0');

        $this->assertEquals(0, $this->config->getNewRelicAppId());
    }

    /**
     * Test type casting for getNewRelicAppId with null value
     */
    public function testGetNewRelicAppIdNull()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_ID)
            ->willReturn(null);

        $this->assertEquals(0, $this->config->getNewRelicAppId());
    }

    /**
     * Test all string casting methods with various input types
     *
     * @dataProvider stringCastingProvider
     */
    public function testStringCastingMethods($method, $configPath, $inputValue, $expectedOutput)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($configPath)
            ->willReturn($inputValue);

        $this->assertEquals($expectedOutput, $this->config->$method());
    }

    /**
     * Data provider for string casting methods
     */
    public static function stringCastingProvider(): array
    {
        return [
            // getNewRelicApiUrl tests
            ['getNewRelicApiUrl', Config::XML_PATH_API_URL, '', ''],
            ['getNewRelicApiUrl', Config::XML_PATH_API_URL, null, ''],
            ['getNewRelicApiUrl', Config::XML_PATH_API_URL, false, ''],
            ['getNewRelicApiUrl', Config::XML_PATH_API_URL, true, '1'],
            ['getNewRelicApiUrl', Config::XML_PATH_API_URL, 123, '123'],

            // getInsightsApiUrl tests
            ['getInsightsApiUrl', Config::XML_PATH_INSIGHTS_API_URL, '', ''],
            ['getInsightsApiUrl', Config::XML_PATH_INSIGHTS_API_URL, false, ''],
            ['getInsightsApiUrl', Config::XML_PATH_INSIGHTS_API_URL, 456, '456'],

            // getNewRelicAccountId tests
            ['getNewRelicAccountId', Config::XML_PATH_ACCOUNT_ID, '', ''],
            ['getNewRelicAccountId', Config::XML_PATH_ACCOUNT_ID, false, ''],
            ['getNewRelicAccountId', Config::XML_PATH_ACCOUNT_ID, 789, '789'],

            // getNewRelicAppName tests
            ['getNewRelicAppName', Config::XML_PATH_APP_NAME, '', ''],
            ['getNewRelicAppName', Config::XML_PATH_APP_NAME, null, ''],
            ['getNewRelicAppName', Config::XML_PATH_APP_NAME, false, ''],
            ['getNewRelicAppName', Config::XML_PATH_APP_NAME, 0, '0'],

            // getApiMode tests
            ['getApiMode', Config::XML_PATH_API_MODE, '', ''],
            ['getApiMode', Config::XML_PATH_API_MODE, false, ''],
            ['getApiMode', Config::XML_PATH_API_MODE, 0, '0'],

            // getEntityGuid tests
            ['getEntityGuid', Config::XML_PATH_ENTITY_GUID, '', ''],
            ['getEntityGuid', Config::XML_PATH_ENTITY_GUID, null, ''],
            ['getEntityGuid', Config::XML_PATH_ENTITY_GUID, false, ''],

            // getNerdGraphUrl tests
            ['getNerdGraphUrl', Config::XML_PATH_NERD_GRAPH_API_URL, '', ''],
            ['getNerdGraphUrl', Config::XML_PATH_NERD_GRAPH_API_URL, null, ''],
            ['getNerdGraphUrl', Config::XML_PATH_NERD_GRAPH_API_URL, false, '']
        ];
    }

    /**
     * Test integer casting for getNewRelicAppId with various input types
     *
     * @dataProvider integerCastingProvider
     */
    public function testGetNewRelicAppIdCasting($inputValue, $expectedOutput)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_APP_ID)
            ->willReturn($inputValue);

        $this->assertEquals($expectedOutput, $this->config->getNewRelicAppId());
    }

    /**
     * Data provider for integer casting
     */
    public static function integerCastingProvider(): array
    {
        return [
            [null, 0],
            ['', 0],
            [false, 0],
            [true, 1],
            ['123', 123],
            ['123.45', 123],  // PHP int casting truncates decimals
            [456.78, 456],
            [0, 0],
            ['0', 0]
        ];
    }

    /**
     * Test boolean casting for isSeparateApps with various input types
     *
     * @dataProvider booleanCastingProvider
     */
    public function testIsSeparateAppsCasting($inputValue, $expectedOutput)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SEPARATE_APPS)
            ->willReturn($inputValue);

        $this->assertEquals($expectedOutput, $this->config->isSeparateApps());
    }

    /**
     * Data provider for boolean casting
     */
    public static function booleanCastingProvider(): array
    {
        return [
            [null, false],
            ['', false],
            [false, false],
            [true, true],
            ['0', false],
            ['1', true],
            [0, false],
            [1, true],
            ['false', true],  // Non-empty string is truthy
            ['true', true],
            [[], false],      // Empty array is falsy
            ['anything', true] // Non-empty string is truthy
        ];
    }

    /**
     * Test that constructor properly assigns all dependencies
     */
    public function testConstructorAssignment()
    {
        // Use reflection to verify private properties are set correctly
        $reflection = new \ReflectionClass($this->config);

        $scopeConfigProperty = $reflection->getProperty('scopeConfig');
        $this->assertSame($this->scopeConfigMock, $scopeConfigProperty->getValue($this->config));

        $encryptorProperty = $reflection->getProperty('encryptor');
        $this->assertSame($this->encryptorMock, $encryptorProperty->getValue($this->config));

        $resourceConfigProperty = $reflection->getProperty('resourceConfig');
        $this->assertSame($this->resourceConfigMock, $resourceConfigProperty->getValue($this->config));
    }

    /**
     * Test comprehensive configuration scenario with all methods
     */
    public function testComprehensiveConfigurationScenario()
    {
        // Mock all getValue calls
        $configValues = [
            Config::XML_PATH_API_URL => 'https://api.newrelic.com/v2/applications/%s/deployments.json',
            Config::XML_PATH_INSIGHTS_API_URL => 'https://insights-collector.newrelic.com/v1/accounts/%s/events',
            Config::XML_PATH_ACCOUNT_ID => '123456',
            Config::XML_PATH_APP_ID => '789012',
            Config::XML_PATH_APP_NAME => 'My Application',
            Config::XML_PATH_API_KEY => 'encrypted_api_key',
            Config::XML_PATH_INSIGHTS_INSERT_KEY => 'encrypted_insights_key',
            Config::XML_PATH_SEPARATE_APPS => '1',
            Config::XML_PATH_API_MODE => 'nerdgraph',
            Config::XML_PATH_ENTITY_GUID => 'ENTITY_GUID_123',
            Config::XML_PATH_NERD_GRAPH_API_URL => 'https://api.newrelic.com/graphql'
        ];

        $this->scopeConfigMock->expects($this->exactly(12))
            ->method('getValue')
            ->willReturnCallback(function ($path) use ($configValues) {
                return $configValues[$path] ?? null;
            });

        // Mock isSetFlag calls
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('isSetFlag')
            ->willReturnCallback(function ($path) {
                $flagValues = [
                    Config::XML_PATH_ENABLED => true,
                    Config::XML_PATH_CRON_ENABLED => true
                ];
                return $flagValues[$path] ?? false;
            });

        // Mock decrypt calls
        $this->encryptorMock->expects($this->exactly(2))
            ->method('decrypt')
            ->willReturnCallback(function ($encryptedValue) {
                $decryptMap = [
                    'encrypted_api_key' => 'NRAK-DECRYPTED-API-KEY',
                    'encrypted_insights_key' => 'NRII-DECRYPTED-INSIGHTS-KEY'
                ];
                return $decryptMap[$encryptedValue] ?? '';
            });

        // Test all methods return expected values
        $this->assertTrue($this->config->isNewRelicEnabled());
        $this->assertEquals(
            'https://api.newrelic.com/v2/applications/%s/deployments.json',
            $this->config->getNewRelicApiUrl()
        );
        $this->assertEquals(
            'https://insights-collector.newrelic.com/v1/accounts/%s/events',
            $this->config->getInsightsApiUrl()
        );
        $this->assertEquals('123456', $this->config->getNewRelicAccountId());
        $this->assertEquals(789012, $this->config->getNewRelicAppId());
        $this->assertEquals('NRAK-DECRYPTED-API-KEY', $this->config->getNewRelicApiKey());
        $this->assertEquals('NRII-DECRYPTED-INSIGHTS-KEY', $this->config->getInsightsInsertKey());
        $this->assertEquals('My Application', $this->config->getNewRelicAppName());
        $this->assertTrue($this->config->isSeparateApps());
        $this->assertTrue($this->config->isCronEnabled());
        $this->assertEquals('nerdgraph', $this->config->getApiMode());
        $this->assertEquals('ENTITY_GUID_123', $this->config->getEntityGuid());
        $this->assertTrue($this->config->isNerdGraphMode());
        $this->assertEquals('https://api.newrelic.com/graphql', $this->config->getNerdGraphUrl());
    }
}

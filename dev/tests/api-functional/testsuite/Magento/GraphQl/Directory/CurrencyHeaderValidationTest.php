<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directory;

use Magento\Catalog\Helper\Data;
use Magento\Config\App\Config\Type\System;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

#[
    Config(Data::XML_PATH_PRICE_SCOPE, Data::PRICE_SCOPE_WEBSITE),
    DataFixture(WebsiteFixture::class, as: 'website'),
    DataFixture(StoreGroupFixture::class, ['website_id' => '$website.id$'], 'group'),
    DataFixture(StoreFixture::class, ['store_group_id' => '$group.id$'], 'store'),
]
class CurrencyHeaderValidationTest extends GraphQlAbstract
{
    private const QUERY = <<<QUERY
    {
        storeConfig {
            store_code
            base_currency_code
            default_display_currency_code
        }
    }
    QUERY;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var int
     */
    private $websiteId;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->websiteId = (int) $this->fixtures->get('website')->getId();

        $configResource = Bootstrap::getObjectManager()->get(ConfigResource::class);
        $configResource->saveConfig(
            Currency::XML_PATH_CURRENCY_ALLOW,
            'NOK',
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteId
        );
        $configResource->saveConfig(
            Currency::XML_PATH_CURRENCY_BASE,
            'NOK',
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteId
        );
        $configResource->saveConfig(
            Currency::XML_PATH_CURRENCY_DEFAULT,
            'NOK',
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteId
        );
        Bootstrap::getObjectManager()->get(System::class)->clean();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $configResource = Bootstrap::getObjectManager()->get(ConfigResource::class);
        $configResource->deleteConfig(
            Currency::XML_PATH_CURRENCY_ALLOW,
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteId
        );
        $configResource->deleteConfig(
            Currency::XML_PATH_CURRENCY_BASE,
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteId
        );
        $configResource->deleteConfig(
            Currency::XML_PATH_CURRENCY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITES,
            $this->websiteId
        );
        Bootstrap::getObjectManager()->get(System::class)->clean();

        parent::tearDown();
    }

    public function testCurrencyAllowedOnRequestedStoreIsAccepted(): void
    {
        $storeCode = $this->fixtures->get('store')->getCode();
        $headers = ['Store' => $storeCode, 'Content-Currency' => 'NOK'];

        $response = $this->graphQlQuery(self::QUERY, [], '', $headers);

        $this->assertSame($storeCode, $response['storeConfig']['store_code']);
        $this->assertSame('NOK', $response['storeConfig']['base_currency_code']);
        $this->assertSame('NOK', $response['storeConfig']['default_display_currency_code']);
    }

    public function testCurrencyNotAllowedOnDefaultStoreIsRejectedWithoutStoreHeader(): void
    {
        $headers = ['Content-Currency' => 'NOK'];

        $this->expectExceptionMessage('GraphQL response contains errors: Please correct the target currency');
        $this->graphQlQuery(self::QUERY, [], '', $headers);
    }

    public function testCurrencyNotAllowedOnRequestedStoreIsRejected(): void
    {
        $storeCode = $this->fixtures->get('store')->getCode();
        $headers = ['Store' => $storeCode, 'Content-Currency' => 'USD'];

        $this->expectExceptionMessage('GraphQL response contains errors: Please correct the target currency');
        $this->graphQlQuery(self::QUERY, [], '', $headers);
    }
}

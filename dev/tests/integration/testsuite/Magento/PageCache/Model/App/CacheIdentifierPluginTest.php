<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\PageCache\Model\App\Request\Http\IdentifierForSave;
use Magento\PageCache\Model\Cache\Type;
use Magento\Store\Model\StoreManager;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for \Magento\PageCache\Model\App\CacheIdentifierPlugin
 */
class CacheIdentifierPluginTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var Identifier
     */
    private $identifier;

    /**
     * @var IdentifierForSave
     */
    private $identifierForSave;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var StateInterface
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $originalCacheState;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->get(Http::class);
        $this->identifier = $this->objectManager->get(Identifier::class);
        $this->identifierForSave = $this->objectManager->get(IdentifierForSave::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->cacheState = $this->objectManager->get(StateInterface::class);

        // Store original cache state
        $this->originalCacheState = $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER);

        // Enable the cache type
        $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, true);
    }

    protected function tearDown(): void
    {
        // Revert cache state to original
        if (isset($this->cacheState) && isset($this->originalCacheState)) {
            $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, $this->originalCacheState);
        }
    }

    /**
     * Test that cache identifier includes run type and run code
     */
    #[
        DbIsolation(false),
        ConfigFixture('system/full_page_cache/caching_application', '1', 'store'),
        ConfigFixture('system/full_page_cache/enabled', '1', 'store'),
        DataFixture(WebsiteFixture::class, as: 'website'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website.id$'], 'store_group'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group.id$'], 'store')
    ]
    public function testAfterGetValueWithRunTypeAndCode()
    {
        $storeCode = $this->fixtures->get('store')->getCode();
        $serverParams = [
            StoreManager::PARAM_RUN_TYPE => 'store',
            StoreManager::PARAM_RUN_CODE => $storeCode
        ];
        $this->request->setServer(new \Laminas\Stdlib\Parameters($serverParams));

        $result = $this->identifier->getValue();
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('MAGE_RUN_TYPE=store', $result);
        $this->assertStringContainsString('MAGE_RUN_CODE=' . $storeCode, $result);

        $resultForSave = $this->identifierForSave->getValue();
        $this->assertNotEmpty($resultForSave);
        $this->assertStringContainsString('MAGE_RUN_TYPE=store', $resultForSave);
        $this->assertStringContainsString('MAGE_RUN_CODE=' . $storeCode, $resultForSave);
    }
}

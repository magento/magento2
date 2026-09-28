<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase\GraphQl;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\ContextFactory;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResolverCacheAbstract extends GraphQlAbstract
{
    /**
     * @var bool
     */
    private $isOriginalResolverCacheEnabled;

    /**
     * @var bool
     */
    private $resolverCacheStatusChanged = false;

    /**
     * @var bool
     */
    private $isOriginalFullPageCacheEnabled;

    /**
     * @var bool
     */
    private $fullPageCacheStatusChanged = false;

    /**
     * @var array|null
     */
    private $originalCacheTypesConfig = null;

    /**
     * @var string
     */
    private $initialAppArea;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        // test has to be executed in graphql area
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        /** @var State $appArea */
        $appArea = $this->objectManager->get(State::class);
        $this->initialAppArea = $appArea->getAreaCode();
        $this->objectManager->configure($configLoader->load(Area::AREA_GRAPHQL));
        $this->mockGuestUserInfoContext();

        // Enable GraphQL resolver cache
        $this->isOriginalResolverCacheEnabled = $this->isCacheEnabled(GraphQlResolverCache::TYPE_IDENTIFIER);
        if (!$this->isOriginalResolverCacheEnabled) {
            $this->resolverCacheStatusChanged = true;
            $this->setCacheTypeStatusEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, true);
        }

        // Disable full page cache
        $this->isOriginalFullPageCacheEnabled = $this->isCacheEnabled(FullPageCache::TYPE_IDENTIFIER);
        if ($this->isOriginalFullPageCacheEnabled) {
            $this->fullPageCacheStatusChanged = true;
            $this->setCacheTypeStatusEnabled(FullPageCache::TYPE_IDENTIFIER, false);
        }

        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        // clean graphql resolver cache and reset to original enablement status
        $this->cleanCacheType(GraphQlResolverCache::TYPE_IDENTIFIER);
        if ($this->resolverCacheStatusChanged) {
            $this->setCacheTypeStatusEnabled(
                GraphQlResolverCache::TYPE_IDENTIFIER,
                $this->isOriginalResolverCacheEnabled
            );
            $this->resolverCacheStatusChanged = false;
        }

        // Reset to original full page cache enablement status
        if ($this->fullPageCacheStatusChanged) {
            $this->setCacheTypeStatusEnabled(
                FullPageCache::TYPE_IDENTIFIER,
                $this->isOriginalFullPageCacheEnabled
            );
            $this->fullPageCacheStatusChanged = false;
        }

        // Restore original cache_types config to prevent test cross-contamination
        if ($this->originalCacheTypesConfig !== null) {
            $writer = $this->objectManager->get(Writer::class);
            $writer->saveConfig(
                [ConfigFilePool::APP_ENV => ['cache_types' => $this->originalCacheTypesConfig]],
                true // override
            );
            
            // Clear DeploymentConfig cache so next test reads the restored env.php
            $this->objectManager->get(DeploymentConfig::class)->resetData();
            
            // CRITICAL: Clear State's in-memory cache and remove shared instance
            // so next test gets a fresh State object that reloads from restored env.php
            $cacheState = $this->objectManager->get(StateInterface::class);
            $cacheState->_resetState();
            $this->objectManager->removeSharedInstance(StateInterface::class);
            
            $this->originalCacheTypesConfig = null;
        }

        /** @var ConfigLoader $configLoader */
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        $this->objectManager->configure($configLoader->load($this->initialAppArea));
        $this->objectManager->removeSharedInstance(ContextFactory::class);
        $this->objectManager->removeSharedInstance(\Magento\GraphQl\Model\Query\Context::class);
        $this->objectManager->removeSharedInstance(\Magento\GraphQl\Model\Query\ContextInterface::class);

        parent::tearDown();
    }

    /**
     * Initialize test-scoped user context with $customer
     *
     * @param CustomerInterface $customer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    #[AllowMockObjectsWithoutExpectations]
    protected function mockCustomerUserInfoContext(CustomerInterface $customer)
    {
        $userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->onlyMethods(['getUserId', 'getUserType'])
            ->disableOriginalConstructor()
            ->getMock();
        $userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($customer->getId());
        $userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        /** @var ContextFactory $contextFactory */
        $contextFactory = $this->objectManager->get(ContextFactory::class);
        $contextFactory->create($userContextMock);
    }

    /**
     * Reset test-scoped user context to guest.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[AllowMockObjectsWithoutExpectations]
    protected function mockGuestUserInfoContext()
    {
        $userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->onlyMethods(['getUserId', 'getUserType'])
            ->disableOriginalConstructor()
            ->getMock();
        $userContextMock->expects($this->any())
            ->method('getUserId')
            ->willReturn(0);
        $userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        // test has to be executed in graphql area
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        $this->objectManager->configure($configLoader->load(Area::AREA_GRAPHQL));
        /** @var ContextFactory $contextFactory */
        $contextFactory = $this->objectManager->get(ContextFactory::class);
        $contextFactory->create($userContextMock);
    }

    /**
     * Get cache status of the given cache type.
     *
     * @param string $cacheType
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCacheEnabled(string $cacheType): bool
    {
        /** @var StateInterface $cacheState */
        $cacheState = $this->objectManager->get(StateInterface::class);
        return $cacheState->isEnabled($cacheType);
    }

    /**
     * Set cache type status.
     *
     * @param string $cacheType
     * @param bool $enable
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setCacheTypeStatusEnabled(string $cacheType, bool $enable): void
    {
        /** @var StateInterface $cacheState */
        $cacheState = $this->objectManager->get(StateInterface::class);
        
        // Save original cache_types config before first persist() call
        if ($this->originalCacheTypesConfig === null) {
            $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
            $this->originalCacheTypesConfig = $deploymentConfig->get('cache_types') ?: [];
        }
        
        $cacheState->setEnabled($cacheType, $enable);
        
        // CRITICAL: For GraphQL cache tests to work, HTTP requests must see the cache enabled
        // We persist to env.php but will restore original state in tearDown()
        $cacheState->persist();
    }

    /**
     * Clean given cache type.
     *
     * @param string $cacheType
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function cleanCacheType(string $cacheType): void
    {
        try {
            // Get a fresh Pool instance without affecting shared instances
            $cachePool = $this->objectManager->create(Pool::class);
            $cache = $cachePool->get($cacheType);

            // Clean all cache entries for this type
            $cache->clean(CacheConstants::CLEANING_MODE_ALL);

            if ($cacheType === GraphQlResolverCache::TYPE_IDENTIFIER) {
                $backend = $cache->getBackend();
                if (method_exists($backend, 'clean')) {
                    $backend->clean(CacheConstants::CLEANING_MODE_ALL);
                }

                $this->objectManager->removeSharedInstance(
                    GraphQlResolverCache::class
                );
            }
        } catch (\Exception $e) {
            // If direct clean fails, use cache manager
            /** @var Manager $cacheManager */
            $cacheManager = $this->objectManager->get(Manager::class);
            $cacheManager->clean([$cacheType]);
        }
    }
}

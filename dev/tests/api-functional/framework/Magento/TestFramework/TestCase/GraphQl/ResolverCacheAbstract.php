<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase\GraphQl;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\ContextFactory;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $out = '';
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento cache:status", $out);
        $cacheStatus = implode('| ', $out);
        preg_match("/(?<cache_type>$cacheType): (?<enabled>\d+)/", $cacheStatus, $matches);
        return !empty($matches) && array_key_exists('enabled', $matches) && $matches['enabled'];
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
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $out = '';
        if ($enable) {
            // phpcs:ignore Magento2.Security.InsecureFunction
            exec("php -f {$appDir}/bin/magento cache:enable $cacheType", $out);
        } else {
            // phpcs:ignore Magento2.Security.InsecureFunction
            exec("php -f {$appDir}/bin/magento cache:disable $cacheType", $out);
        }
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
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $out = '';

        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento cache:clean $cacheType", $out);
    }
}

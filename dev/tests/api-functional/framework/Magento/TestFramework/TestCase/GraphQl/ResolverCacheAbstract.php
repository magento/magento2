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
use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\ContextFactory;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\TestFramework\App\State;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResolverCacheAbstract extends GraphQlAbstract
{
    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var CacheStateInterface
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $originalCacheStateEnabledStatus;

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
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // test has to be executed in graphql area
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        /** @var State $appArea */
        $appArea = $this->objectManager->get(State::class);
        $this->initialAppArea = $appArea->getAreaCode();
        $this->objectManager->configure($configLoader->load(Area::AREA_GRAPHQL));
        $this->mockGuestUserInfoContext();

        $this->cacheState = $this->objectManager->get(CacheStateInterface::class);
        $this->originalCacheStateEnabledStatus = $this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER);
        $this->cacheState->setEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, true);
        $this->graphQlResolverCache = $this->objectManager->get(GraphQlResolverCache::class);

        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        // clean graphql resolver cache and reset to original enablement status
        $this->graphQlResolverCache->clean();
        $this->cacheState->setEnabled(
            GraphQlResolverCache::TYPE_IDENTIFIER,
            $this->originalCacheStateEnabledStatus
        );

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
}

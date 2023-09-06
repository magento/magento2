<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Plugin\Resolver;

use Magento\Framework\App\Cache\State as CacheState;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\GraphQlResolverCache\Model\Plugin\Resolver\Cache as CachePlugin;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\IdentityInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ResolverIdentityClassProvider;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type;
use Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CacheTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var GraphQlRequest
     */
    private $graphQlRequest;

    /**
     * @var CacheState
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $origCacheEnabled;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var Type|\PHPUnit\Framework\MockObject\MockObject
     */
    private $graphqlResolverCacheMock;

    /**
     * @var Type
     */
    private $graphQlResolverCache;

    /**
     * @var GenericFactorProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $keyFactorMock;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->cacheState = $this->objectManager->get(CacheState::class);
        $this->origCacheEnabled = $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER);
        if (!$this->origCacheEnabled) {
            $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, true);
            $this->cacheState->persist();
        }
        $this->graphQlResolverCache = $this->objectManager->get(Type::class);
        $this->graphQlResolverCache->clean();
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, $this->origCacheEnabled);
        $this->cacheState->persist();
        $this->graphQlResolverCache->clean();
        parent::tearDown();
    }

    /**
     * @magentoAppArea graphql
     */
    public function testCachingSkippedOnKeyCalculationFailure()
    {
        $this->preconfigureMocks();
        $this->configurePlugin();
        $this->keyFactorMock->expects($this->any())
            ->method('getFactorValue')
            ->willThrowException(new \Exception("Test key factor exception"));
        $this->graphqlResolverCacheMock->expects($this->never())
            ->method('load');
        $this->graphqlResolverCacheMock->expects($this->never())
            ->method('save');
        $this->graphQlRequest->send($this->getTestQuery());
    }

    /**
     * @magentoAppArea graphql
     */
    public function testCachingNotSkippedWhenKeysOk()
    {
        $this->preconfigureMocks();
        $this->configurePlugin();
        $this->loggerMock->expects($this->never())->method('warning');
        $this->graphqlResolverCacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->graphqlResolverCacheMock->expects($this->once())
            ->method('save');
        $this->graphQlRequest->send($this->getTestQuery());
    }

    /**
     * Configure mocks and object manager for test.
     *
     * @return void
     */
    private function preconfigureMocks()
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['warning'])
            ->setMockClassName('CacheLoggerMockForTest')
            ->getMockForAbstractClass();

        $this->graphqlResolverCacheMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'save'])
            ->setMockClassName('GraphqlResolverCacheMockForTest')
            ->getMock();

        $this->keyFactorMock = $this->getMockBuilder(GenericFactorProviderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactorValue', 'getFactorName'])
            ->setMockClassName('TestFailingKeyFactor')
            ->getMock();

        $this->objectManager->addSharedInstance($this->keyFactorMock, 'TestFailingKeyFactor');

        $this->objectManager->configure(
            [
                Calculator::class => [
                    'arguments' => [
                        'factorProviders' => [
                            'test_failing' => 'TestFailingKeyFactor'
                        ]
                    ]
                ]
            ]
        );

        $this->objectManager->configure(
            [
                \Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\Provider::class => [
                    'arguments' => [
                        'factorProviders' => [
                            \Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver::class => [
                                'test_failing' => 'TestFailingKeyFactor'
                            ]
                        ]
                    ]
                ]
            ]
        );

        $identityProviderMock = $this->getMockBuilder(IdentityInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIdentities'])
            ->setMockClassName('TestIdentityProvider')
            ->getMock();

        $identityProviderMock->expects($this->any())
            ->method('getIdentities')
            ->willReturn(['test_identity']);

        $this->objectManager->addSharedInstance($identityProviderMock, 'TestIdentityProvider');

        $this->objectManager->configure(
            [
                ResolverIdentityClassProvider::class => [
                    'arguments' => [
                        'cacheableResolverClassNameIdentityMap' => [
                            StoreConfigResolver::class => 'TestIdentityProvider'
                        ]
                    ]
                ]
            ]
        );
    }

    private function getTestQuery()
    {
        return <<<QUERY
{
  storeConfig {
    id,
    code,
    store_code,
    store_name
  }
}
QUERY;
    }

    /**
     * Reset plugin for the resolver.
     *
     * @return void
     */
    private function configurePlugin()
    {
        // need to reset plugins list to inject new plugin with mocks as it is cached at runtime
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->get(PluginList::class);
        $pluginList->reset();
        $this->objectManager->removeSharedInstance(CachePlugin::class);
        $this->objectManager->addSharedInstance(
            $this->objectManager->create(CachePlugin::class, [
                'logger' => $this->loggerMock,
                'graphQlResolverCache' => $this->graphqlResolverCacheMock
            ]),
            CachePlugin::class
        );
    }
}

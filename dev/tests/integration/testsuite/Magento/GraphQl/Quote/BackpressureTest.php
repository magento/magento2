<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactoryInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\SlidingWindowEnforcer;
use Magento\Framework\App\Backpressure\SlidingWindow\ValkeyRequestLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Backpressure\BackpressureContextFactory;
use Magento\GraphQl\Model\Backpressure\BackpressureFieldValidator;
use Magento\GraphQl\Model\Backpressure\GraphQlTooManyRequestsException;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\QuoteGraphQl\Model\Resolver\SetPaymentAndPlaceOrder;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackpressureTest extends TestCase
{
    /**
     * @var BackpressureContextFactory
     */
    private $contextFactory;

    /**
     * @var LimitConfigManagerInterface
     */
    private $limitConfigManager;

    /**
     * @var IdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->identityProvider = $this->createStub(IdentityProviderInterface::class);
        $this->contextFactory = Bootstrap::getObjectManager()->create(
            BackpressureContextFactory::class,
            ['identityProvider' => $this->identityProvider]
        );
        $this->limitConfigManager = Bootstrap::getObjectManager()->get(LimitConfigManagerInterface::class);
    }

    /**
     * Configured cases.
     *
     * @return array
     */
    public static function getConfiguredCases(): array
    {
        return [
            'guest' => [
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1',
                SetPaymentAndPlaceOrder::class,
                50
            ],
            'customer' => [
                ContextInterface::IDENTITY_TYPE_CUSTOMER,
                '42',
                PlaceOrder::class,
                100
            ]
        ];
    }

    /**
     * Verify that backpressure is configured for guests.
     *
     * @param int $identityType
     * @param string $identity
     * @param string $resolver
     * @param int $expectedLimit
     */
    #[
        DataProvider('getConfiguredCases'),
        Config('sales/backpressure/enabled', 1, ScopeInterface::SCOPE_STORE),
        Config('sales/backpressure/limit', 100, ScopeInterface::SCOPE_STORE),
        Config('sales/backpressure/guest_limit', 50, ScopeInterface::SCOPE_STORE),
        Config('sales/backpressure/period', 60, ScopeInterface::SCOPE_STORE),
    ]
    public function testConfigured(
        int $identityType,
        string $identity,
        string $resolver,
        int $expectedLimit
    ): void {
        $this->identityProvider->method('fetchIdentityType')->willReturn($identityType);
        $this->identityProvider->method('fetchIdentity')->willReturn($identity);

        $field = $this->createStub(Field::class);
        $field->method('getResolver')->willReturn($resolver);
        $context = $this->contextFactory->create($field);
        $this->assertEquals(OrderLimitConfigManager::REQUEST_TYPE_ID, $context->getTypeId());

        $limits = $this->limitConfigManager->readLimit($context);
        $this->assertEquals($expectedLimit, $limits->getLimit());
        $this->assertEquals(60, $limits->getPeriod());
    }

    /**
     * The valkey logger type must resolve via DI instead of throwing (ACP2E-4899 root cause fix).
     */
    public function testValkeyLoggerFactoryResolvesToValkeyRequestLogger(): void
    {
        $realFactory = Bootstrap::getObjectManager()->get(RequestLoggerFactoryInterface::class);
        $this->assertInstanceOf(ValkeyRequestLogger::class, $realFactory->create('valkey'));
    }

    /**
     * Cases covering every identity/resolver/limit combination called out in ACP2E-4899:
     * guest and customer place order, both rate-limited (over the configured limit) and
     * allowed through (within the configured limit).
     *
     * @return array
     */
    public static function getValkeyEnforcementCases(): array
    {
        return [
            'guest_place_order_rate_limited' => [
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1',
                SetPaymentAndPlaceOrder::class,
                1,
                2,
                true,
            ],
            'guest_place_order_within_limit' => [
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1',
                SetPaymentAndPlaceOrder::class,
                5,
                3,
                false,
            ],
            'customer_place_order_rate_limited' => [
                ContextInterface::IDENTITY_TYPE_CUSTOMER,
                '42',
                PlaceOrder::class,
                1,
                2,
                true,
            ],
            'customer_place_order_within_limit' => [
                ContextInterface::IDENTITY_TYPE_CUSTOMER,
                '42',
                PlaceOrder::class,
                5,
                3,
                false,
            ],
        ];
    }

    /**
     * GraphQL place order must return a "Too Many Requests" rate-limit error once the configured
     * limit is exceeded, and must let requests through normally while under the limit, when
     * valkey is the backpressure logger — for both guest and customer identities. Pre-fix, the
     * unrecognized 'valkey' logger type made enforcement silently no-op instead of either behavior
     * (regression test for ACP2E-4899 / ACQE-9902).
     *
     * @param int $identityType
     * @param string $identity
     * @param string $resolver
     * @param int $limit
     * @param int $simulatedCount
     * @param bool $expectRateLimited
     */
    #[
        DataProvider('getValkeyEnforcementCases'),
        Config('sales/backpressure/enabled', 1, ScopeInterface::SCOPE_STORE),
    ]
    public function testPlaceOrderEnforcementWithValkeyLogger(
        int $identityType,
        string $identity,
        string $resolver,
        int $limit,
        int $simulatedCount,
        bool $expectRateLimited
    ): void {
        $loggedErrors = [];
        $psrLogger = $this->createStub(LoggerInterface::class);
        $psrLogger->method('error')->willReturnCallback(
            function (string $message) use (&$loggedErrors): void {
                $loggedErrors[] = $message;
            }
        );

        // Simulate the valkey-backed request logger reporting the current sliding-window count.
        $requestLogger = $this->createStub(RequestLoggerInterface::class);
        $requestLogger->method('incrAndGetFor')->willReturn($simulatedCount);
        $requestLogger->method('getFor')->willReturn(null);

        $requestLoggerFactory = $this->createMock(RequestLoggerFactoryInterface::class);
        $requestLoggerFactory->expects($this->once())
            ->method('create')
            ->with('valkey')
            ->willReturn($requestLogger);

        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $deploymentConfig->expects($this->atLeastOnce())
            ->method('get')
            ->with(RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER)
            ->willReturn('valkey');

        $limitConfigManager = $this->createStub(LimitConfigManagerInterface::class);
        $limitConfigManager->method('readLimit')->willReturn(new LimitConfig($limit, 60));

        $enforcer = Bootstrap::getObjectManager()->create(
            SlidingWindowEnforcer::class,
            [
                'requestLoggerFactory' => $requestLoggerFactory,
                'configManager' => $limitConfigManager,
                'deploymentConfig' => $deploymentConfig,
                'logger' => $psrLogger,
            ]
        );

        $validator = Bootstrap::getObjectManager()->create(
            BackpressureFieldValidator::class,
            [
                'backpressureContextFactory' => $this->contextFactory,
                'backpressureEnforcer' => $enforcer,
            ]
        );

        $this->identityProvider->method('fetchIdentityType')->willReturn($identityType);
        $this->identityProvider->method('fetchIdentity')->willReturn($identity);

        $field = $this->createStub(Field::class);
        $field->method('getResolver')->willReturn($resolver);

        if ($expectRateLimited) {
            try {
                $validator->validate($field, []);
                $this->fail('Expected GraphQlTooManyRequestsException was not thrown.');
            } catch (GraphQlTooManyRequestsException $exception) {
                $this->assertEquals('Too Many Requests', $exception->getMessage());
            }
        } else {
            $validator->validate($field, []);
            $this->addToAssertionCount(1);
        }

        $this->assertEmpty(
            array_filter($loggedErrors, fn(string $msg) => str_contains($msg, 'Invalid request logger type')),
            'GraphQL place order must not log "Invalid request logger type" when valkey is configured.'
        );
    }
}

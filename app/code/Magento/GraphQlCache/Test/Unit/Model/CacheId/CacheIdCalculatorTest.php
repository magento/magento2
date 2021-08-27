<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Test\Unit\Model\CacheId;

use Exception;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Math\Random;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test CacheIdCalculator
 */
class CacheIdCalculatorTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ContextFactoryInterface|MockObject
     */
    private $contextFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var DeploymentConfig\Writer|MockObject
     */
    private $envWriter;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var CacheIdFactorProviderInterface|MockObject
     */
    private $factorProvider1;

    /**
     * @var CacheIdFactorProviderInterface|MockObject
     */
    private $factorProvider2;

    protected function setup(): void
    {
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->contextFactory = $this->getMockForAbstractClass(ContextFactoryInterface::class);
        $context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->contextFactory->expects($this->any())->method('get')->willReturn($context);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->envWriter = $this->getMockBuilder(DeploymentConfig\Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->random = new Random();
        $this->factorProvider1 = $this->getMockForAbstractClass(CacheIdFactorProviderInterface::class);
        $this->factorProvider1->expects($this->any())->method('getFactorName')->willReturn('FACTOR_ONE_NAME');
        $this->factorProvider1->expects($this->any())->method('getFactorValue')->willReturn('FACTOR_ONE_VALUE');
        $this->factorProvider2 = $this->getMockForAbstractClass(CacheIdFactorProviderInterface::class);
        $this->factorProvider2->expects($this->any())->method('getFactorName')->willReturn('FACTOR_TWO_NAME');
        $this->factorProvider2->expects($this->any())->method('getFactorValue')->willReturn('FACTOR_TWO_VALUE');
    }

    /**
     * Test that the cache id is null if there are no providers
     */
    public function testNoProviders()
    {
        $calculator = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            []
        );

        $result = $calculator->getCacheId();
        $this->assertNull($result);
    }

    /**
     * Test that the cache id is null and a warning is logged if something failed
     */
    public function testFailedToGenerate()
    {
        $calculator = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            [$this->factorProvider1, $this->factorProvider2]
        );

        $this->deploymentConfig->expects($this->once())->method('get')->willThrowException(new Exception('Error!'));
        $this->logger->expects($this->once())->method('warning');

        $result = $calculator->getCacheId();
        $this->assertNull($result);
    }

    /**
     * Test that a new salt isn't written if an existing salt is already configured
     */
    public function testExistingSalt()
    {
        $calculator = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            [$this->factorProvider1, $this->factorProvider2]
        );

        $this->deploymentConfig->expects($this->once())->method('get')->willReturn('SALT_VALUE');
        $this->envWriter->expects($this->never())->method('saveConfig');

        $result = $calculator->getCacheId();
        $this->assertNotEmpty($result);
    }

    /**
     * Test that the cache id is encrypted in some way
     */
    public function testIdEncrypted()
    {
        $calculator = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            [$this->factorProvider1, $this->factorProvider2]
        );

        $this->deploymentConfig->expects($this->once())->method('get')->willReturn('SALT_VALUE');

        $result = $calculator->getCacheId();
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('SALT_VALUE', $result);
    }

    /**
     * Test that the cache id is the same no matter what order the providers are injected in
     */
    public function testFactorsSorted()
    {
        $this->deploymentConfig->expects($this->any())->method('get')->willReturn('SALT_VALUE');

        $calc1 = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            [$this->factorProvider1, $this->factorProvider2]
        );
        $result1 = $calc1->getCacheId();

        $calc2 = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            [$this->factorProvider2, $this->factorProvider1]
        );
        $result2 = $calc2->getCacheId();

        $this->assertNotEmpty($result1);
        $this->assertNotEmpty($result2);
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test that a new salt is written to the env file if no salt already exists in the environment config
     */
    public function testSaltSaves()
    {
        $calculator = new CacheIdCalculator(
            $this->logger,
            $this->contextFactory,
            $this->deploymentConfig,
            $this->envWriter,
            $this->random,
            [$this->factorProvider1, $this->factorProvider2]
        );

        $this->deploymentConfig->expects($this->once())->method('get')->willReturn(null);
        $this->envWriter->expects($this->once())->method('saveConfig');

        $result = $calculator->getCacheId();
        $this->assertNotEmpty($result);
    }
}

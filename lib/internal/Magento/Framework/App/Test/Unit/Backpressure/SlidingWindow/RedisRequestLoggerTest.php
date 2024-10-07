<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger;
use Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger\RedisClient;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RedisRequestLoggerTest extends TestCase
{
    /**
     * @var RedisRequestLogger
     */
    private RedisRequestLogger $redisRequestLogger;

    /**
     * @var RedisClient|MockObject
     */
    private $redisClientMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->redisClientMock = $this->createMock(RedisClient::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->deploymentConfigMock->method('get')
            ->with('backpressure/logger/id-prefix', 'reqlog')
            ->willReturn('custompref_');
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->contextMock->method('getTypeId')
            ->willReturn('typeId_');
        $this->contextMock->method('getIdentityType')
            ->willReturn(2);
        $this->contextMock->method('getIdentity')
            ->willReturn('_identity_');

        $this->redisRequestLogger = new RedisRequestLogger(
            $this->redisClientMock,
            $this->deploymentConfigMock
        );
    }

    public function testIncrAndGetFor()
    {
        $expectedId = 'custompref_typeId_2_identity_400';

        $this->redisClientMock->method('incrBy')
            ->with($expectedId, 1);
        $this->redisClientMock->method('expireAt')
            ->with($expectedId, time() + 500);
        $this->redisClientMock->method('exec')
            ->willReturn(['45']);

        self::assertEquals(
            45,
            $this->redisRequestLogger->incrAndGetFor($this->contextMock, 400, 500)
        );
    }

    public function testGetFor()
    {
        $expectedId = 'custompref_typeId_2_identity_600';
        $this->redisClientMock->method('get')
        ->with($expectedId)
        ->willReturn('23');

        self::assertEquals(23, $this->redisRequestLogger->getFor($this->contextMock, 600));
    }
}

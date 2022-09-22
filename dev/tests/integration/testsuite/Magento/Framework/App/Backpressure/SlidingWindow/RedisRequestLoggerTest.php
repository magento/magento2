<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 *  Tests CacheRequestLogger class
 */
class RedisRequestLoggerTest extends TestCase
{
    /**
     * @var RedisRequestLogger
     */
    private $model;

    /**
     * @var int
     */
    private $contextCounter = 0;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = Bootstrap::getObjectManager()->get(RedisRequestLogger::class);
    }

    /**
     * Verify that counter is increased for a new random context.
     *
     * @return void
     */
    public function testIncrAndGetForNewIncremented(): void
    {
        $timeSlot = time();
        $context = $this->generateContext();

        for ($expected = 1; $expected <= 10; $expected++) {
            $this->assertEquals($expected, $this->model->incrAndGetFor($context, $timeSlot, 3600));
        }

        //Another context
        $this->assertEquals(1, $this->model->incrAndGetFor($this->generateContext(), $timeSlot, 3600));
        //Another slot
        $this->assertEquals(1, $this->model->incrAndGetFor($context, $timeSlot + 3600, 3600));
    }

    /**
     * Verify that correct counter can be read.
     *
     * @return void
     */
    public function testGetForNewCorrect(): void
    {
        $timeSlot = time();
        $context = $this->generateContext();

        for ($expected = 1; $expected <= 10; $expected++) {
            $this->model->incrAndGetFor($context, $timeSlot, 3600);
            $this->assertEquals($expected, $this->model->getFor($context, $timeSlot));
        }

        //Another context
        $this->model->incrAndGetFor($anotherContext = $this->generateContext(), $timeSlot, 3600);
        $this->assertEquals(1, $this->model->getFor($anotherContext, $timeSlot));
        //Another slot
        $this->model->incrAndGetFor($context, $anotherSlot = $timeSlot + 3600, 3600);
        $this->assertEquals(1, $this->model->getFor($context, $anotherSlot));
    }

    /**
     * Generate context instance.
     *
     * @return ContextInterface
     */
    private function generateContext(): ContextInterface
    {
        $mock = $this->createMock(ContextInterface::class);
        $mock->method('getRequest')->willReturn(Bootstrap::getObjectManager()->get(RequestInterface::class));
        $mock->method('getIdentity')
            ->willReturn('127.0.' .random_int(1, 255) .'.' .(++$this->contextCounter));
        $mock->method('getIdentityType')->willReturn(ContextInterface::IDENTITY_TYPE_IP);
        $mock->method('getTypeId')->willReturn('test');

        return $mock;
    }
}

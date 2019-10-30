<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Async\Test\Unit;

use Magento\Framework\Async\CallbackDeferred;
use Magento\Framework\Async\CancelingDeferredException;
use PHPUnit\Framework\TestCase;

/**
 * Test for deferred callbacks.
 */
class CallbackDeferredTest extends TestCase
{
    /**
     * @var string|null
     */
    private $value;

    /**
     * @var callable
     */
    private $function;

    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * @var CallbackDeferred
     */
    private $deferred;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->value = null;
        $this->function = function () {
            if ($this->exception) {
                throw $this->exception;
            }
            $this->value = 'success';

            return $this->value;
        };
        $this->deferred = new CallbackDeferred($this->function);
    }

    /**
     * Test successfully executing a function.
     */
    public function testSuccess(): void
    {
        $this->assertFalse($this->deferred->isDone());
        $this->assertFalse($this->deferred->isCancelled());
        $this->assertEmpty($this->value);
        $value = $this->deferred->get();
        $this->assertNotEmpty($value);
        $this->assertEquals($this->value, $value);
        $this->assertTrue($this->deferred->isDone());
        $this->assertFalse($this->deferred->isCancelled());

        //Function is not being executed.
        $this->value = null;
        $value = $this->deferred->get();
        $this->assertEmpty($this->value);
        $this->assertNotEmpty($value);
        $exception = null;
        try {
            $this->deferred->cancel(true);
        } catch (CancelingDeferredException $ex) {
            $exception = $ex;
        }
        $this->assertNotEmpty($exception);
        $this->assertFalse($this->deferred->isCancelled());
    }

    /**
     * Test function with exception;
     */
    public function testException(): void
    {
        $this->exception = new \RuntimeException();

        $this->assertFalse($this->deferred->isDone());
        $this->assertFalse($this->deferred->isCancelled());
        $this->assertEmpty($this->value);
        $exception = null;
        try {
            $this->deferred->get();
        } catch (\RuntimeException $ex) {
            $exception = $ex;
        }
        $this->assertEquals($this->exception, $exception);
        $this->assertTrue($this->deferred->isDone());
        $this->assertFalse($this->deferred->isCancelled());
        $this->assertEmpty($this->value);

        //Continues to throw the exception.
        try {
            $this->deferred->get();
        } catch (\RuntimeException $ex) {
            $exception = $ex;
        }
        $this->assertEquals($this->exception, $exception);
    }

    /**
     * Test canceling the deferred.
     */
    public function testCanceling(): void
    {
        $this->assertFalse($this->deferred->isDone());
        $this->assertFalse($this->deferred->isCancelled());

        $this->deferred->cancel();
        $this->assertEmpty($this->value);
        $this->assertTrue($this->deferred->isCancelled());
        $this->assertFalse($this->deferred->isDone());
        $exception = null;
        try {
            $this->deferred->get();
        } catch (CancelingDeferredException $ex) {
            $exception = $ex;
        }
        $this->assertNotEmpty($exception);
        $this->assertEmpty($this->value);
        $this->assertTrue($this->deferred->isCancelled());
        $this->assertFalse($this->deferred->isDone());
    }
}

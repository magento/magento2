<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Async;

/**
 * Executes given callback when get() is used.
 */
class CallbackDeferred implements CancelableDeferredInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var bool
     */
    private $canceled = false;

    /**
     * @var bool
     */
    private $done = false;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * CallbackDeferred constructor.
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function cancel(bool $force = false): void
    {
        if ($this->isDone()) {
            throw new CancelingDeferredException('Already executed');
        }
        if ($this->isCancelled()) {
            throw new CancelingDeferredException('Already canceled');
        }

        $this->canceled = true;
    }

    /**
     * @inheritDoc
     */
    public function isCancelled(): bool
    {
        return $this->canceled;
    }

    /**
     * Return deferred value.
     *
     * @return mixed
     * @throws \Throwable
     */
    private function returnResults()
    {
        if ($this->exception) {
            throw $this->exception;
        }

        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        if ($this->isCancelled()) {
            throw new CancelingDeferredException('Deferred operation is canceled');
        }

        if (!$this->isDone()) {
            try {
                $this->value = ($this->callback)();
            } catch (\Throwable $exception) {
                $this->exception = $exception;
            }
            $this->done = true;
        }

        return $this->returnResults();
    }

    /**
     * @inheritDoc
     */
    public function isDone(): bool
    {
        return $this->done;
    }
}

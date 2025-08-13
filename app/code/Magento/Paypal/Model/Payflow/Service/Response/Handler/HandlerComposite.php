<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;

class HandlerComposite implements HandlerInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers = [];

    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        array_walk(
            $handlers,
            function ($handler, $code) {
                if (!$handler instanceof HandlerInterface) {
                    $message = 'Type mismatch. Expected type: %s. Actual: %s, Code: %s';

                    throw new \LogicException(
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        sprintf($message, 'HandlerInterface', gettype($handler), $code)
                    );
                }
            }
        );

        $this->handlers = $handlers;
    }

    /**
     * @inheritDoc
     */
    public function handle(InfoInterface $payment, DataObject $response)
    {
        foreach ($this->handlers as $handle) {
            $handle->handle($payment, $response);
        }
    }
}

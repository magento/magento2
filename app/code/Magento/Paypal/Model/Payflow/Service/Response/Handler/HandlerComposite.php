<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
                        sprintf($message, 'HandlerInterface', gettype($handler), $code)
                    );
                }
            }
        );

        $this->handlers = $handlers;
    }

    /**
     * {inheritdoc}
     */
    public function handle(InfoInterface $payment, DataObject $response)
    {
        foreach ($this->handlers as $handle) {
            $handle->handle($payment, $response);
        }
    }
}

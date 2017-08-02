<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Response;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class HandlerChain
 * @package Magento\Payment\Gateway\Response
 * @api
 * @since 2.0.0
 */
class HandlerChain implements HandlerInterface
{
    /**
     * @var HandlerInterface[] | TMap
     * @since 2.0.0
     */
    private $handlers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $handlers
     * @since 2.0.0
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers = []
    ) {
        $this->handlers = $tmapFactory->create(
            [
                'array' => $handlers,
                'type' => HandlerInterface::class
            ]
        );
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @since 2.0.0
     */
    public function handle(array $handlingSubject, array $response)
    {
        foreach ($this->handlers as $handler) {
            // @TODO implement exceptions catching
            $handler->handle($handlingSubject, $response);
        }
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface as SaveHandlerInterface;

/**
 * @api Instantiate save handler when implementing custom Indexer\Action
 * @since 2.0.0
 */
class SaveHandlerFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get handler class instance
     *
     * @param string $saveHandlerClass
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return IndexerInterface
     * @since 2.0.0
     */
    public function create($saveHandlerClass, $arguments = [])
    {
        $handler = $this->objectManager->create($saveHandlerClass, $arguments);
        if (!$handler instanceof SaveHandlerInterface) {
            throw new \InvalidArgumentException(
                $saveHandlerClass . ' doesn\'t implement \Magento\Framework\IndexerInterface'
            );
        }

        return $handler;
    }
}

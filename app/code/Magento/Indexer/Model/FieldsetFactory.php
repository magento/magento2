<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

class FieldsetFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create fieldset class instance
     *
     * @param string $handlerClass
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return FieldsetInterface
     */
    public function create($handlerClass, array $arguments = [])
    {
        $handler = $this->objectManager->create($handlerClass, $arguments);
        if (!$handler instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                $handlerClass . ' doesn\'t implement \Magento\Indexer\Model\FieldsetInterface'
            );
        }

        return $handler;
    }
}

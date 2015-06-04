<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

class SourceFactory
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
     * Get source class instance by class name
     *
     * @param string $className
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return SourceInterface
     */
    public function create($className, array $arguments = [])
    {
        $source = $this->objectManager->create($className, $arguments);
        if (!$source instanceof SourceInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Indexer\Model\SourceInterface'
            );
        }

        return $source;
    }
}

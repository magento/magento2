<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;

class StructureFactory
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
     * Get structure class instance by class name
     *
     * @param string $className
     * @param [] $arguments
     * @throws \InvalidArgumentException
     * @return IndexStructureInterface
     */
    public function create($className, $arguments = [])
    {
        $structure = $this->objectManager->create($className, $arguments);
        if (!$structure instanceof IndexStructureInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\Indexer\IndexStructureInterface'
            );
        }

        return $structure;
    }
}

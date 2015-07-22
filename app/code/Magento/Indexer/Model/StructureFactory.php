<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

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
                $className . ' doesn\'t implement \Magento\Indexer\Model\IndexStructureInterface'
            );
        }

        return $structure;
    }
}

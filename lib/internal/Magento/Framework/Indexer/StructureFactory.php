<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * Class \Magento\Framework\Indexer\StructureFactory
 *
 * @since 2.0.0
 */
class StructureFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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

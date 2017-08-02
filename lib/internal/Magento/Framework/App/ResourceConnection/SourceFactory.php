<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

/**
 * Class \Magento\Framework\App\ResourceConnection\SourceFactory
 *
 * @since 2.0.0
 */
class SourceFactory
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
     * Get source class instance by class name
     *
     * @param string $className
     * @throws \InvalidArgumentException
     * @return SourceProviderInterface
     * @since 2.0.0
     */
    public function create($className)
    {
        $source = $this->objectManager->create($className);
        if (!$source instanceof SourceProviderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\App\ResourceConnection\SourceProviderInterface'
            );
        }

        return $source;
    }
}

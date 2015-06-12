<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Indexer\Model\Source\DataInterface as SourceDataInterface;
use Magento\Indexer\Model\Source\DataInterface;

class SourcePool
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
     * @throws \InvalidArgumentException
     * @return SourceInterface|DataInterface
     */
    public function get($className)
    {
        $source = $this->objectManager->get($className);
        if (!$source instanceof SourceInterface && !$source instanceof SourceDataInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Indexer\Model\SourceInterface'
            );
        }

        return $source;
    }
}

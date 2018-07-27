<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\FileCollector;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class AggregatedFileCollectorFactory
 */
class AggregatedFileCollectorFactory
{
    const INSTANCE_NAME = 'Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create config reader
     *
     * @param array $arguments
     * @return AggregatedFileCollector
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $arguments);
    }
}

<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Mview\View\AdditionalColumnsProcessor;

use Magento\Framework\Mview\View\AdditionalColumnProcessorInterface;
use Magento\Framework\ObjectManagerInterface;

class ProcessorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ProcessorFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Instantiate additional columns processor
     *
     * @param string $processorClassName
     * @return AdditionalColumnProcessorInterface
     */
    public function create(string $processorClassName): AdditionalColumnProcessorInterface
    {
        return $this->objectManager->create($processorClassName);
    }
}

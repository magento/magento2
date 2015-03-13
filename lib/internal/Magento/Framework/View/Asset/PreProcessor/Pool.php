<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * A registry of asset preprocessors (not to confuse with the "Registry" pattern)
 */
class Pool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $preProcessorClasses = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $preProcessors
     */
    public function __construct(ObjectManagerInterface $objectManager, array $preProcessors = [])
    {
        $this->objectManager = $objectManager;
        $this->preProcessorClasses = $preProcessors;
    }

    /**
     * Execute preprocessors instances suitable to convert source content type into a destination one
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        $fromType = $chain->getOrigContentType();
        $toType = $chain->getTargetContentType();
        foreach ($this->getPreProcessors($fromType, $toType) as $preProcessor) {
            $preProcessor->process($chain);
        }
    }

    /**
     * Retrieve preProcessors by types
     *
     * @param string $fromType
     * @param string $toType
     * @return PreProcessorInterface[]
     */
    private function getPreProcessors($fromType, $toType)
    {
        $preProcessors = [];
        if (isset($this->preProcessorClasses[$fromType]) && isset($this->preProcessorClasses[$fromType][$toType])) {
            $preProcessors = $this->preProcessorClasses[$fromType][$toType];
        } else {
            $preProcessors[] = 'Magento\Framework\View\Asset\PreProcessor\Passthrough';
        }

        $processorInstances = [];
        foreach ($preProcessors as $preProcessor) {
            $processorInstance = $this->objectManager->get($preProcessor);
            if (!$processorInstance instanceof PreProcessorInterface) {
                throw new \UnexpectedValueException("{$preProcessor} has to implement the PreProcessorInterface.");
            }
            $processorInstances[] = $processorInstance;
        }

        return $processorInstances;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessorFactory;
use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * A registry of asset preprocessors (not to confuse with the "Registry" pattern)
 */
class Pool
{
    /**
     * @var PreProcessorFactory
     */
    private $processorFactory;

    /**
     * @var PreProcessorInterface[]
     */
    private $preProcessors = [];

    /**
     * @param PreProcessorFactory $processorFactory
     * @param array $preProcessors
     */
    public function __construct(PreProcessorFactory $processorFactory, array $preProcessors = [])
    {
        $this->processorFactory = $processorFactory;
        $this->preProcessors = $preProcessors;
    }

    /**
     * Execute preprocessors instances suitable to convert source content type into a destination one
     *
     * @param Chain $chain
     */
    public function process(Chain $chain)
    {
        $processorClasses = [];
        if (isset($this->preProcessors[$chain->getOrigContentType()])) {
            $processorClasses =
                isset($this->preProcessors[$chain->getOrigContentType()][$chain->getTargetContentType()])
                ? $this->preProcessors[$chain->getOrigContentType()][$chain->getTargetContentType()] : [];
        } else {
            $this->processorFactory->create('Magento\Framework\View\Asset\PreProcessor\Passthrough')->process($chain);
        }
        foreach($processorClasses as $processorClass) {
            $this->processorFactory->create($processorClass)->process($chain);
        }
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\Model\Exception;
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
     * @var PreProcessorInterface[]
     */
    private $preProcessors = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $preProcessors
     */
    public function __construct(ObjectManagerInterface $objectManager, array $preProcessors = null)
    {
        $this->objectManager = $objectManager;
        $this->preProcessors = $preProcessors;
    }

    /**
     * Retrieve preprocessors instances suitable to convert source content type into a destination one
     *
     * BUG: this implementation is hard-coded intentionally because there is a logic duplication that needs to be fixed.
     * Adding an extensibility layer through DI configuration would add even more fragility to this design.
     * If you need to add another preprocessor, use interceptors or class inheritance (at your own risk).
     *
     * @param string $sourceContentType
     * @param string $targetContentType
     * @throws Exception
     * @return PreProcessorInterface[]
     */
    public function getPreProcessors($sourceContentType, $targetContentType)
    {
        $result = [];
        if (!isset($this->preProcessors[$sourceContentType][$targetContentType])) {
            throw new \LogicException('Preprocessor from [' . $sourceContentType . '] to [' . $targetContentType .'] isn\'t defined');
        }
        foreach($this->preProcessors[$sourceContentType][$targetContentType] as $processorClass) {
            $preProcessor = $this->objectManager->get($processorClass);
            if (!$preProcessor instanceof PreProcessorInterface) {
                throw new Exception($preProcessor . ' doesn\'t implement \Magento\Framework\View\Asset\PreProcessorInterface');
            }
            $result[] = $preProcessor;
        }

        return $result;
    }
}

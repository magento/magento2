<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    const PREPROCESSOR_CLASS = 'class';

    /**
     * @var array
     */
    private $preprocessors;

    /**
     * @var array
     */
    private $instances;

    /**
     * @var Helper\SortInterface
     */
    private $sorter;

    /**
     * @var string
     */
    private $defaultPreprocessor;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Helper\SortInterface $sorter
     * @param string $defaultPreprocessor
     * @param array $preprocessors
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Helper\SortInterface $sorter,
        $defaultPreprocessor,
        array $preprocessors = []
    ) {
        $this->preprocessors = $preprocessors;
        $this->sorter = $sorter;
        $this->defaultPreprocessor = $defaultPreprocessor;
        $this->objectManager = $objectManager;
    }

    /**
     * Execute preprocessors instances suitable to convert source content type into a destination one
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        $type = $chain->getTargetContentType();
        foreach ($this->getPreProcessors($type) as $preProcessor) {
            $preProcessor->process($chain);
        }
    }

    /**
     * Retrieve preProcessors by types
     *
     * @param string $type
     * @return PreProcessorInterface[]
     * @throws \UnexpectedValueException
     */
    private function getPreProcessors($type)
    {
        if (isset($this->instances[$type])) {
            return $this->instances[$type];
        }

        if (isset($this->preprocessors[$type])) {
            $preprocessors = $this->sorter->sort($this->preprocessors[$type]);
        } else {
            $preprocessors = [
                'default' => [self::PREPROCESSOR_CLASS => $this->defaultPreprocessor]
            ];
        }

        $this->instances[$type] = [];
        foreach ($preprocessors as $preprocessor) {
            $instance = $this->objectManager->get($preprocessor[self::PREPROCESSOR_CLASS]);
            if (!$instance instanceof PreProcessorInterface) {
                throw new \UnexpectedValueException(
                    '"' . $preprocessor[self::PREPROCESSOR_CLASS] . '" has to implement the PreProcessorInterface.'
                );
            }
            $this->instances[$type][] = $instance;
        }

        return $this->instances[$type];
    }
}

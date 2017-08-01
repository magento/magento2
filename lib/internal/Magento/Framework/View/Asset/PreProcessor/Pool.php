<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * A registry of asset preprocessors (not to confuse with the "Registry" pattern)
 * @since 2.0.0
 */
class Pool
{
    /**
     * Name of property referenced to pre-processor implementation class
     */
    const PREPROCESSOR_CLASS = 'class';

    /**
     * @var array
     * @since 2.0.0
     */
    private $preprocessors;

    /**
     * @var array
     * @since 2.0.0
     */
    private $instances;

    /**
     * @var Helper\SortInterface
     * @since 2.0.0
     */
    private $sorter;

    /**
     * @var string
     * @since 2.0.0
     */
    private $defaultPreprocessor;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Helper\SortInterface $sorter
     * @param string $defaultPreprocessor
     * @param array $preprocessors
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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

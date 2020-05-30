<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\SimpleDirective;

/**
 * Container for defined list of simple processors
 */
class ProcessorPool
{
    /**
     * @var array|ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof ProcessorInterface) {
                throw new \InvalidArgumentException(
                    'Simple processors must implement ' . ProcessorInterface::class
                );
            }
        }

        $this->processors = $processors;
    }

    /**
     * Retrieve a defined processor from the pool by name
     *
     * @param string $name
     * @return ProcessorInterface
     */
    public function get(string $name): ProcessorInterface
    {
        if (empty($this->processors[$name])) {
            throw new \InvalidArgumentException('Processor with key "' . $name . '" has not been defined');
        }

        return $this->processors[$name];
    }
}

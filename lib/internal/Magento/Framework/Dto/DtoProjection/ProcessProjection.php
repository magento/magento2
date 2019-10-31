<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\DtoProjection;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Perform DTO projection
 */
class ProcessProjection
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Run DTO projection against a list of processors
     *
     * @param array $data
     * @param ProcessorInterface[] $processors
     * @param array $originalData
     * @return array
     */
    public function execute(array $data, array $processors, array $originalData): array
    {
        foreach ($processors as $processorClassName) {
            $processor = $this->objectManager->get($processorClassName);
            if (!($processor instanceof ProcessorInterface)) {
                throw new InvalidArgumentException(
                    'Processor ' . $processor . ' must implement ' . ProcessorInterface::class
                );
            }

            $data = $processor->execute($data, $originalData);
        }

        return $data;
    }
}

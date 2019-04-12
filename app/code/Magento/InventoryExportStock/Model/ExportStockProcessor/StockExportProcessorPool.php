<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ExportStockProcessor;

use InvalidArgumentException;

/**
 * Class ProcessorPool provides processor by it's type
 */
class StockExportProcessorPool
{
    /**
     * @var array
     */
    private $processors;

    /**
     * StockExportProcessorPool constructor
     *
     * @param array $processors
     * @throws InvalidArgumentException
     */
    public function __construct(
        array $processors
    ) {
        $this->processors = $processors;
        foreach ($this->processors as $processor) {
            if (!$processor instanceof ExportStockProcessorInterface) {
                throw new InvalidArgumentException(
                    __('One of processor is not instance of StockExportProcessorInterface class')
                );
            }
        }
    }

    /**
     * Provides processor by it's type
     *
     * @param string $processorsName
     * @return ExportStockProcessorInterface
     */
    public function getStockExportProcessorByName(string $processorsName): ExportStockProcessorInterface
    {
        foreach ($this->processors as $name => $processor) {
            if ($processorsName === $name) {
                return $processor;
            }
        }
        throw new InvalidArgumentException(
            __('Processor with such name is absent')
        );
    }
}

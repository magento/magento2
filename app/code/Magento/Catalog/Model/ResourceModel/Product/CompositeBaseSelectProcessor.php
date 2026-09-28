<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\InputException;

/**
 * Class CompositeBaseSelectProcessor
 */
class CompositeBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var BaseSelectProcessorInterface[]
     */
    private $baseSelectProcessors;

    /**
     * @param BaseSelectProcessorInterface[] $baseSelectProcessors
     * @throws InputException
     */
    public function __construct(
        array $baseSelectProcessors
    ) {
        foreach ($baseSelectProcessors as $baseSelectProcessor) {
            if (!$baseSelectProcessor instanceof BaseSelectProcessorInterface) {
                throw new InputException(
                    __('Processor %1 doesn\'t implement BaseSelectProcessorInterface', get_class($baseSelectProcessor))
                );
            }
        }
        $this->baseSelectProcessors = $baseSelectProcessors;
    }

    /**
     * @param Select $select
     * @return Select
     */
    public function process(Select $select)
    {
        foreach ($this->baseSelectProcessors as $baseSelectProcessor) {
            $select = $baseSelectProcessor->process($select);
        }
        return $select;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\InputException;

/**
 * Class CompositeBaseSelectProcessor
 * @since 2.1.3
 */
class CompositeBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var BaseSelectProcessorInterface[]
     * @since 2.1.3
     */
    private $baseSelectProcessors;

    /**
     * @param BaseSelectProcessorInterface[] $baseSelectProcessors
     * @throws InputException
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function process(Select $select)
    {
        foreach ($this->baseSelectProcessors as $baseSelectProcessor) {
            $select = $baseSelectProcessor->process($select);
        }
        return $select;
    }
}

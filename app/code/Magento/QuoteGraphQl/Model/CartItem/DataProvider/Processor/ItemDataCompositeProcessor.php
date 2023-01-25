<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * {@inheritdoc}
 */
class ItemDataCompositeProcessor implements ItemDataProcessorInterface
{
    /**
     * @var ItemDataProcessorInterface[]
     */
    private $itemDataProcessors;

    /**
     * @param ItemDataProcessorInterface[] $itemDataProcessors
     */
    public function __construct(array $itemDataProcessors = [])
    {
        $this->itemDataProcessors = $itemDataProcessors;
    }

    /**
     * Process cart item data
     *
     * @param array $cartItemData
     * @param ContextInterface $context
     * @return array
     */
    public function process(array $cartItemData, ContextInterface $context): array
    {
        foreach ($this->itemDataProcessors as $itemDataProcessor) {
            $cartItemData = $itemDataProcessor->process($cartItemData, $context);
        }

        return $cartItemData;
    }
}

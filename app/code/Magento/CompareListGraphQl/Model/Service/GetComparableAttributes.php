<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\CompareListGraphQl\Model\Service\Collection\GetComparableItemsCollection as ComparableItemsCollection;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Get products comparable attributes
 */
class GetComparableAttributes
{
    /**
     * @var ComparableItemsCollection
     */
    private $comparableItemsCollection;

    /**
     * @param ComparableItemsCollection $comparableItemsCollection
     */
    public function __construct(
        ComparableItemsCollection $comparableItemsCollection
    ) {
        $this->comparableItemsCollection = $comparableItemsCollection;
    }

    /**
     * Get comparable attributes
     *
     * @param int $listId
     * @param ContextInterface $context
     *
     * @return array
     */
    public function execute(int $listId, ContextInterface $context): array
    {
        $attributes = [];
        $itemsCollection = $this->comparableItemsCollection->execute($listId, $context);
        foreach ($itemsCollection->getComparableAttributes() as $item) {
            $attributes[] = [
                'code' => $item->getAttributeCode(),
                'label' => $item->getStoreLabel()
            ];
        }

        return $attributes;
    }
}

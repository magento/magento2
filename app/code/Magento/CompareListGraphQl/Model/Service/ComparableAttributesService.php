<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\CompareListGraphQl\Model\Service\Collection\ComparableItems as ComparableItemsCollection;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Get comparable attributes
 */
class ComparableAttributesService
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
    public function getComparableAttributes(int $listId, ContextInterface $context): array
    {
        $attributes = [];
        $itemsCollection = $this->comparableItemsCollection->getCollectionComparableItems($listId, $context);
        foreach ($itemsCollection->getComparableAttributes() as $item) {
            $attributes[] = [
                'code' => $item->getAttributeCode(),
                'title' => $item->getStoreLabel()
            ];
        }

        return $attributes;
    }
}

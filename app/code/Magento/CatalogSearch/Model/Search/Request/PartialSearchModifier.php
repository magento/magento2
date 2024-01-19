<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\Request;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Modifies partial search query in search requests configuration
 */
class PartialSearchModifier implements ModifierInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function modify(array $requests): array
    {
        $attributes = $this->getSearchableAttributes();
        foreach ($requests as $code => $request) {
            $matches = $request['queries']['partial_search']['match'] ?? [];
            if ($matches) {
                foreach ($matches as $index => $match) {
                    $field = $match['field'] ?? null;
                    if ($field && $field !== '*') {
                        if (!isset($attributes[$field])) {
                            unset($matches[$index]);
                            continue;
                        }
                        $matches[$index]['boost'] = $attributes[$field]->getSearchWeight() ?: 1;
                    }
                }
                $requests[$code]['queries']['partial_search']['match'] = array_values($matches);
            }
        }
        return $requests;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return Attribute[]
     */
    private function getSearchableAttributes(): array
    {
        $attributes = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ['is_searchable', 'is_visible_in_advanced_search', 'is_filterable', 'is_filterable_in_search'],
            [1, 1, [1, 2], 1]
        );

        /** @var Attribute $attribute */
        foreach ($collection->getItems() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        return $attributes;
    }
}

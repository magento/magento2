<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export;

use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\EntityFiltersProviderInterface;

class EntityFiltersProvider implements EntityFiltersProviderInterface
{
    /**
     * @param array[] $additionalFilters
     */
    public function __construct(
        private readonly array $additionalFilters = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getFilters(Export $export): \Magento\Framework\Data\Collection
    {
        $collection = $export->filterAttributeCollection($export->getEntityAttributeCollection());
        foreach ($this->additionalFilters as $data) {
            $attribute = $collection->getNewEmptyItem();
            $attribute->setId($data['attribute_code']);
            foreach ($data as $key => $value) {
                $attribute->setDataUsingMethod($key, $value);
            }
            $collection->addItem($attribute);
        }

        return $collection;
    }
}

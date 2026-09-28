<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\ImportExport\Model\Export;

class EntityFiltersProvider implements EntityFiltersProviderInterface
{
    /**
     * @param EntityFiltersProviderInterface[] $providers
     */
    public function __construct(
        private readonly array $providers = []
    ) {
        // validate that all providers implement the interface
        array_map(fn (EntityFiltersProviderInterface $provider) => $provider, $this->providers);
    }

    /**
     * @inheritDoc
     */
    public function getFilters(Export $export): \Magento\Framework\Data\Collection
    {
        return isset($this->providers[$export->getEntity()])
            ? $this->providers[$export->getEntity()]->getFilters($export)
            : $export->filterAttributeCollection($export->getEntityAttributeCollection());
    }
}

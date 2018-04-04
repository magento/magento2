<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Layer;

use Magento\Catalog\Model\Layer\FilterListFactory;
use Magento\Catalog\Model\Layer\Resolver;

/**
 * Layer types filters provider.
 *
 * @package Magento\CatalogGraphQl\Model\Resolver\Layer
 */
class FiltersProvider
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Layer\FilterableAttributesListFactory
     */
    private $filterableAttributesListFactory;

    /**
     * @var FilterListFactory
     */
    private $filterListFactory;

    /**
     * FiltersProvider constructor.
     * @param Resolver $layerResolver
     * @param \Magento\CatalogGraphQl\Model\Resolver\Layer\FilterableAttributesListFactory $filterableAttributesListFactory
     * @param FilterListFactory $filterListFactory
     */
    public function __construct(
        Resolver $layerResolver,
        FilterableAttributesListFactory $filterableAttributesListFactory,
        FilterListFactory $filterListFactory
    ) {

        $this->layerResolver = $layerResolver;
        $this->filterableAttributesListFactory = $filterableAttributesListFactory;
        $this->filterListFactory = $filterListFactory;
    }

    /**
     * Get layer type filters.
     *
     * @param string $layerType
     * @return array
     */
    public function getFilters(string $layerType) : array
    {
        $filterableAttributesList = $this->filterableAttributesListFactory->create(
            $layerType
        );
        $filterList = $this->filterListFactory->create(
            [
                'filterableAttributes' => $filterableAttributesList
            ]
        );
        return $filterList->getFilters($this->layerResolver->get());
    }
}

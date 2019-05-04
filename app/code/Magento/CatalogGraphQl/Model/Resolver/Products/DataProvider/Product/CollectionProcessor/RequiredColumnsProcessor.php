<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Add attributes required for every GraphQL product resolution process.
 *
 * {@inheritdoc}
 */
class RequiredColumnsProcessor implements CollectionProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        $collection->addAttributeToSelect('special_price');
        $collection->addAttributeToSelect('special_price_from');
        $collection->addAttributeToSelect('special_price_to');
        $collection->addAttributeToSelect('tax_class_id');

        return $collection;
    }
}

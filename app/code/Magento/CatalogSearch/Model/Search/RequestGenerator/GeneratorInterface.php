<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * @api
 * @since 2.2.0
 */
interface GeneratorInterface
{
    /**
     * Get filter data for specific attribute
     * @param Attribute $attribute
     * @param string $filterName
     * @return array
     * @since 2.2.0
     */
    public function getFilterData(Attribute $attribute, $filterName);

    /**
     * Get aggregation data for specific attribute
     * @param Attribute $attribute
     * @param string $bucketName
     * @return array
     * @since 2.2.0
     */
    public function getAggregationData(Attribute $attribute, $bucketName);
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

interface GeneratorInterface
{
    /**
     * Get filter data for specific attribute
     * @param Attribute $attribute
     * @param string $filterName
     * @return array
     */
    public function getFilterData(Attribute $attribute, $filterName);

    /**
     * Get aggregation data for specific attribute
     * @param Attribute $attribute
     * @param string $bucketName
     * @return array
     */
    public function getAggregationData(Attribute $attribute, $bucketName);
}

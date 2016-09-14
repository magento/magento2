<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;


use Magento\Catalog\Model\Entity\Attribute;

interface GeneratorInterface
{
    /**
     * Generate filter data for specific attribute
     * @param Attribute $attribute
     * @param string $filterName
     * @return array
     */
    public function getFilterData(Attribute $attribute, $filterName);

    /**
     * Generate aggregations data for specific attribute
     * @param Attribute $attribute
     * @param string $bucketName
     * @return array
     */
    public function getAggregationData(Attribute $attribute, $bucketName);
}

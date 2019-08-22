<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Catalog search range request generator.
 */
class Decimal implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function getFilterData(Attribute $attribute, $filterName)
    {
        return [
            'type' => FilterInterface::TYPE_RANGE,
            'name' => $filterName,
            'field' => $attribute->getAttributeCode(),
            'from' => '$' . $attribute->getAttributeCode() . '.from$',
            'to' => '$' . $attribute->getAttributeCode() . '.to$',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAggregationData(Attribute $attribute, $bucketName)
    {
        return [
            'type' => BucketInterface::TYPE_DYNAMIC,
            'name' => $bucketName,
            'field' => $attribute->getAttributeCode(),
            'method' => 'manual',
            'metric' => [['type' => 'count']],
        ];
    }
}

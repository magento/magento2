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
 * Class \Magento\CatalogSearch\Model\Search\RequestGenerator\General
 *
 * @since 2.2.0
 */
class General implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getFilterData(Attribute $attribute, $filterName)
    {
        return [
            'type' => FilterInterface::TYPE_TERM,
            'name' => $filterName,
            'field' => $attribute->getAttributeCode(),
            'value' => '$' . $attribute->getAttributeCode() . '$',
        ];
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getAggregationData(Attribute $attribute, $bucketName)
    {
        return [
            'type' => BucketInterface::TYPE_TERM,
            'name' => $bucketName,
            'field' => $attribute->getAttributeCode(),
            'metric' => [['type' => 'count']],
        ];
    }
}

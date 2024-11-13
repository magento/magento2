<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * Price attribute aggregation algorithm
     */
    private const AGGREGATION_ALGORITHM_VARIABLE = 'price_dynamic_algorithm';

    /**
     * Default decimal attribute aggregation algorithm
     */
    private const DEFAULT_AGGREGATION_ALGORITHM = 'manual';

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
            'method' => $attribute->getFrontendInput() === 'price'
                ? '$' . self::AGGREGATION_ALGORITHM_VARIABLE . '$'
                : self::DEFAULT_AGGREGATION_ALGORITHM,
            'metric' => [['type' => 'count']],
        ];
    }
}

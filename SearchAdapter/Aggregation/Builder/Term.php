<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

class Term implements BucketBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        /*$facetSet = $baseQueryResult->getFacetSet();
        $values = [];
        /** @var \Solarium\QueryType\Select\Result\Facet\Field $backet */
        /*foreach ($facetSet->getFacet($bucket->getName()) as $name => $count) {
            $values[$name] = [
                'value' => $name,
                'count' => $count,
            ];
        }
        return $values;*/
        return [];
    }
}

<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Aggregations\Category;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Layered navigation filters resolver, used for GraphQL request processing.
 */
class Aggregations implements ResolverInterface
{
    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var Category\IncludeDirectChildrenOnly
     */
    private $includeDirectChildrenOnly;

    /**
     * @param LayerBuilder $layerBuilder
     * @param PriceCurrency $priceCurrency
     * @param Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly
     */
    public function __construct(
        LayerBuilder $layerBuilder,
        ?PriceCurrency $priceCurrency = null,
        ?Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly = null
    ) {
        $this->layerBuilder = $layerBuilder;
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrency::class);
        $this->includeDirectChildrenOnly = $includeDirectChildrenOnly
            ?: ObjectManager::getInstance()->get(Category\IncludeDirectChildrenOnly::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['layer_type']) || !isset($value['search_result'])) {
            return null;
        }

        $aggregations = $value['search_result']->getSearchAggregation();
        if (!$aggregations || (int)$value['total_count'] == 0) {
            return [];
        }

        $categoryFilter = $value['categories'] ?? [];
        $includeDirectChildrenOnly = $args['filter']['category']['includeDirectChildrenOnly'] ?? false;
        if ($includeDirectChildrenOnly && !empty($categoryFilter)) {
            $this->includeDirectChildrenOnly->setFilter(['category' => $categoryFilter]);
        }
        
        $results = $this->layerBuilder->build(
            $aggregations,
            (int)$context->getExtensionAttributes()->getStore()->getId()
        );
        if (!isset($results['price_bucket']['options'])) {
            return $results;
        }

        $priceBucketOptions = [];
        foreach ($results['price_bucket']['options'] as $optionValue) {
            $priceBucketOptions[] = $this->getConvertedAndRoundedOptionValue($optionValue);
        }
        $results['price_bucket']['options'] = $priceBucketOptions;

        return $results;
    }

    /**
     * Converts and rounds option value
     *
     * @param String[] $optionValue
     * @return String[]
     */
    private function getConvertedAndRoundedOptionValue(array $optionValue): array
    {
        list($from, $to) = explode('-', $optionValue['label']);
        $newLabel = $this->priceCurrency->convertAndRound($from) . '-' . $this->priceCurrency->convertAndRound($to);
        $optionValue['label'] = $newLabel;
        $optionValue['value'] = str_replace('-', '_', $newLabel);
        return $optionValue;
    }
}

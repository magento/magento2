<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Aggregations\IncludeSubcategoriesOnly;
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
     * @var Layer\DataProvider\Filters
     */
    private $filtersDataProvider;

    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var IncludeSubcategoriesOnly
     */
    private $includeSubcategoriesOnly;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider
     * @param LayerBuilder $layerBuilder
     * @param PriceCurrency $priceCurrency
     * @param IncludeSubcategoriesOnly $includeSubcategoriesOnly
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider,
        LayerBuilder $layerBuilder,
        PriceCurrency $priceCurrency = null,
        IncludeSubcategoriesOnly $includeSubcategoriesOnly = null
    ) {
        $this->filtersDataProvider = $filtersDataProvider;
        $this->layerBuilder = $layerBuilder;
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrency::class);
        $this->includeSubcategoriesOnly = $includeSubcategoriesOnly
            ?: ObjectManager::getInstance()->get(IncludeSubcategoriesOnly::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['layer_type']) || !isset($value['search_result'])) {
            return null;
        }

        $aggregations = $value['search_result']->getSearchAggregation();

        if ($aggregations) {
            $categoryFilter = $value['categories'] ?? [];
            $includeSubcategoriesOnly = $args['filter']['includeSubcategoriesOnly'] ?? false;
            if ($includeSubcategoriesOnly && !empty($categoryFilter)) {
                $this->includeSubcategoriesOnly->setFilter(['category' => $categoryFilter]);
            }
            /** @var StoreInterface $store */
            $store = $context->getExtensionAttributes()->getStore();
            $storeId = (int)$store->getId();
            $results = $this->layerBuilder->build($aggregations, $storeId);
            if (isset($results['price_bucket'])) {
                foreach ($results['price_bucket']['options'] as &$value) {
                    list($from, $to) = explode('-', $value['label']);
                    $newLabel = $this->priceCurrency->convertAndRound($from)
                        . '-'
                        . $this->priceCurrency->convertAndRound($to);
                    $value['label'] = $newLabel;
                    $value['value'] = str_replace('-', '_', $newLabel);
                }
            }
            return $results;
        } else {
            return [];
        }
    }
}

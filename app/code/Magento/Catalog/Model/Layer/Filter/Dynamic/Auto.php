<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Catalog\Model\Layer\Filter\Price\Render;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\Search\Dynamic\Algorithm;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Auto implements AlgorithmInterface
{
    const MIN_RANGE_POWER = 10;

    /**
     * @var Algorithm
     * @since 2.0.0
     */
    private $algorithm;

    /**
     * @var \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    private $layer;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * @var Render
     * @since 2.0.0
     */
    private $render;

    /**
     * @var Registry
     * @since 2.0.0
     */
    private $coreRegistry;

    /**
     * @var Range
     * @since 2.0.0
     */
    private $range;

    /**
     * @var Price
     * @since 2.0.0
     */
    private $resource;

    /**
     * @param Algorithm $algorithm
     * @param Resolver $layerResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Render $render
     * @param Registry $coreRegistry
     * @param Price $resource
     * @param Range $range
     * @since 2.0.0
     */
    public function __construct(
        Algorithm $algorithm,
        Resolver $layerResolver,
        ScopeConfigInterface $scopeConfig,
        Render $render,
        Registry $coreRegistry,
        Price $resource,
        Range $range
    ) {
        $this->algorithm = $algorithm;
        $this->layer = $layerResolver->get();
        $this->scopeConfig = $scopeConfig;
        $this->render = $render;
        $this->coreRegistry = $coreRegistry;
        $this->range = $range;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '')
    {
        $data = [];
        if (empty($intervals)) {
            $range = $this->range->getPriceRange();
            if (!$range) {
                $range = $this->getRange();
                $dbRanges = $this->resource->getCount($range);
                $data = $this->render->renderRangeData($range, $dbRanges);
            }
        }

        return $data;
    }

    /**
     * @return number
     * @since 2.0.0
     */
    private function getRange()
    {
        $maxPrice = $this->getMaxPriceInt();
        $index = 1;
        do {
            $range = pow(10, strlen(floor($maxPrice)) - $index);
            $items = $this->resource->getCount($range);
            $index++;
        } while ($range > self::MIN_RANGE_POWER && count($items) < 2);

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @return float
     * @since 2.0.0
     */
    public function getMaxPriceInt()
    {
        $maxPrice = $this->layer->getProductCollection()
            ->getMaxPrice();
        $maxPrice = floor($maxPrice);

        return $maxPrice;
    }
}

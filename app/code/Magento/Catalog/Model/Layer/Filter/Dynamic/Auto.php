<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 */
class Auto implements AlgorithmInterface
{
    const MIN_RANGE_POWER = 10;

    /**
     * @var Algorithm
     */
    private $algorithm;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Render
     */
    private $render;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Range
     */
    private $range;

    /**
     * @var Price
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
     */
    public function getMaxPriceInt()
    {
        $maxPrice = $this->layer->getProductCollection()
            ->getMaxPrice();
        $maxPrice = floor($maxPrice);

        return $maxPrice;
    }
}

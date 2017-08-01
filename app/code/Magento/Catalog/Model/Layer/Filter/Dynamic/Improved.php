<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Render;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Catalog\Model\Layer\Filter\Dynamic\Improved
 *
 * @since 2.0.0
 */
class Improved implements AlgorithmInterface
{
    const XML_PATH_INTERVAL_DIVISION_LIMIT = 'catalog/layered_navigation/interval_division_limit';

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
     * @var IntervalFactory
     * @since 2.0.0
     */
    private $intervalFactory;

    /**
     * @param Algorithm $algorithm
     * @param Resolver $layerResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Render $render
     * @param IntervalFactory $intervalFactory
     * @since 2.0.0
     */
    public function __construct(
        Algorithm $algorithm,
        Resolver $layerResolver,
        ScopeConfigInterface $scopeConfig,
        Render $render,
        IntervalFactory $intervalFactory
    ) {
        $this->algorithm = $algorithm;
        $this->layer = $layerResolver->get();
        $this->scopeConfig = $scopeConfig;
        $this->render = $render;
        $this->intervalFactory = $intervalFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '')
    {
        $collection = $this->layer->getProductCollection();
        $appliedInterval = $intervals;
        if ($appliedInterval && $collection->getPricesCount() <= $this->getIntervalDivisionLimit()) {
            return [];
        }
        $this->algorithm->setStatistics(
            $collection->getMinPrice(),
            $collection->getMaxPrice(),
            $collection->getPriceStandardDeviation(),
            $collection->getPricesCount()
        );

        if ($appliedInterval) {
            if ($appliedInterval[0] == $appliedInterval[1] || $appliedInterval[1] === '0') {
                return [];
            }
            $this->algorithm->setLimits($appliedInterval[0], $appliedInterval[1]);
        }
        $interval = $this->intervalFactory->create();
        $items = [];
        foreach ($this->algorithm->calculateSeparators($interval) as $separator) {
            $items[] = [
                'label' => $this->render->renderRangeLabel($separator['from'], $separator['to']),
                'value' => ($separator['from'] == 0 ? ''
                        : $separator['from']) . '-' . $separator['to'] . $additionalRequestData,
                'count' => $separator['count'],
            ];
        }

        return $items;
    }

    /**
     * Get interval division limit
     *
     * @return int
     * @since 2.0.0
     */
    private function getIntervalDivisionLimit()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_INTERVAL_DIVISION_LIMIT, ScopeInterface::SCOPE_STORE);
    }
}

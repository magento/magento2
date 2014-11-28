<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Layer\Filter\Dynamic;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Catalog\Model\Layer\Filter\Price\Render;
use Magento\Catalog\Model\Resource\Layer\Filter\Price;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Store\Model\ScopeInterface;

class Manual implements AlgorithmInterface
{
    const XML_PATH_RANGE_MAX_INTERVALS = 'catalog/layered_navigation/price_range_max_intervals';

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
     * @param int[] $intervals
     * @param string $additionalRequestData
     * @return array
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '')
    {
        $data = [];
        if (empty($intervals)) {
            $range = $this->range->getPriceRange();
            if (!$range) {
                $range = $this->range->getConfigRangeStep();
                $dbRanges = $this->resource->getCount($range);
                $dbRanges = $this->processRange($dbRanges);
                $data = $this->render->renderRangeData($range, $dbRanges);
            }
        }

        return $data;
    }

    /**
     * @param array $items
     * @return array
     */
    private function processRange($items)
    {
        $i = 0;
        $lastIndex = null;
        $maxIntervalsNumber = $this->getMaxIntervalsNumber();
        foreach ($items as $k => $v) {
            ++$i;
            if ($i > 1 && $i > $maxIntervalsNumber) {
                $items[$lastIndex] += $v;
                unset($items[$k]);
            } else {
                $lastIndex = $k;
            }
        }
        return $items;
    }

    /**
     * Get maximum number of intervals
     *
     * @return int
     */
    public function getMaxIntervalsNumber()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_RANGE_MAX_INTERVALS,
            ScopeInterface::SCOPE_STORE
        );
    }
}

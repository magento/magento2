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
namespace Magento\CatalogSearch\Model\Price;

use Magento\Framework\Search\Dynamic\IntervalInterface;

class Interval implements IntervalInterface
{
    /**
     * @var \Magento\Catalog\Model\Resource\Layer\Filter\Price
     */
    private $resource;

    /**
     * @param \Magento\Catalog\Model\Resource\Layer\Filter\Price $resource
     */
    public function __construct(\Magento\Catalog\Model\Resource\Layer\Filter\Price $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $prices = $this->resource->loadPrices($limit, $offset, $lower, $upper);
        return $this->arrayValuesToFloat($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        $prices = $this->resource->loadPreviousPrices($data, $index, $lower);
        return $this->arrayValuesToFloat($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        $prices = $this->resource->loadNextPrices($data, $rightIndex, $upper);
        return $this->arrayValuesToFloat($prices);
    }

    /**
     * @param array $prices
     * @return array
     */
    private function arrayValuesToFloat($prices)
    {
        $returnPrices = [];
        if (is_array($prices) && !empty($prices)) {
            $returnPrices = array_map('floatval', $prices);
        }
        return $returnPrices;
    }
}

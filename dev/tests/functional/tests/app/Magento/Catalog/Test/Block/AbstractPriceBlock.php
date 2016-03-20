<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Base Price Block.
 */
abstract class AbstractPriceBlock extends Block
{
    /**
     * Mapping for different type of price.
     *
     * @var array
     */
    protected $mapTypePrices = [];

    /**
     * Get specify type price.
     *
     * @param string $type
     * @param string $currency [optional]
     * @return string|null
     */
    protected function getTypePrice($type, $currency = '$')
    {
        $typePriceElement = $this->getTypePriceElement($type);
        return $typePriceElement->isVisible() ? $this->trimPrice($typePriceElement->getText(), $currency) : null;
    }

    /**
     * Get specify type price element.
     *
     * @param string $type
     * @return SimpleElement
     */
    protected function getTypePriceElement($type)
    {
        $mapTypePrice = $this->mapTypePrices[$type];
        return $this->_rootElement->find(
            $mapTypePrice['selector'],
            isset($mapTypePrice['strategy']) ? $mapTypePrice['strategy'] : Locator::SELECTOR_CSS
        );
    }

    /**
     * Escape currency and separator for price.
     *
     * @param string $price
     * @param string $currency
     * @return string
     */
    protected function trimPrice($price, $currency = '$')
    {
        return str_replace([',', $currency], '', $price);
    }
}

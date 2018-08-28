<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View\Options\View;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Represent necessary logic for checkbox and radio button option type
 */
class Checkable extends AbstractOptions
{
    protected $_template = 'Magento_Catalog::product/composite/fieldset/options/view/checkable.phtml';

    /**
     * @param $value
     * @return string
     */
    public function formatPrice(ProductCustomOptionValuesInterface $value) : string
    {

        return parent::_formatPrice(
            [
                'is_percent' => $value->getPriceType() === 'percent',
                'pricing_value' => $value->getPrice($value->getPriceType() === 'percent')
            ]
        );
    }

    /**
     * @param $value
     * @return float
     */
    public function getCurrencyByStore(ProductCustomOptionValuesInterface $value) : float
    {
        return $this->pricingHelper->currencyByStore(
            $value->getPrice(true),
            $this->getProduct()->getStore(),
            false
        );
    }

    /**
     * @param $option
     * @return string|array|null
     */
    public function getPreconfiguredValue($option)
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
    }
}

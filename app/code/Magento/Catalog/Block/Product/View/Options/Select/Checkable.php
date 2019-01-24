<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View\Options\Select;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Model\Product\Option;

/**
 * Represent necessary logic for checkbox and radio button option type
 */
class Checkable extends AbstractOptions
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/composite/fieldset/options/view/checkable.phtml';

    /**
     * @param ProductCustomOptionValuesInterface $value
     * @return string
     */
    public function formatPrice(ProductCustomOptionValuesInterface $value)
    {
        return parent::_formatPrice(
            [
                'is_percent' => $value->getPriceType() === 'percent',
                'pricing_value' => $value->getPrice($value->getPriceType() === 'percent')
            ]
        );
    }

    /**
     * @param ProductCustomOptionValuesInterface $value
     * @return float|string
     */
    public function getCurrencyByStore(ProductCustomOptionValuesInterface $value)
    {
        return $this->pricingHelper->currencyByStore(
            $value->getPrice(true),
            $this->getProduct()->getStore(),
            false
        );
    }

    /**
     * @param Option $option
     * @return string|array|null
     */
    public function getPreconfiguredValue(Option $option)
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
    }
}

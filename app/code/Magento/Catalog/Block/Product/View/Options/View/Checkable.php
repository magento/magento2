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
 * Class Checkable
 * @package Magento\Catalog\Block\Product\View\Options\View
 */
class Checkable extends AbstractOptions
{
    protected $_template = 'Magento_Catalog::catalog/product/composite/fieldset/options/view/checkable.phtml';

    /**
     * Checkable constructor.
     * @param Context $context
     * @param Data $pricingHelper
     * @param CatalogHelper $catalogData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $pricingHelper,
        CatalogHelper $catalogData,
        array $data = []
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * @param $value
     * @return string
     */
    public function formatPrice(array $value) : string
    {
        return parent::_formatPrice($value);
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
}

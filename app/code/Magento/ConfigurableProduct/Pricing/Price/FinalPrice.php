<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        if ($this->product->getSelectedConfigurableOption()) {
            $this->amount = null;
        }
        return parent::getAmount();
    }
}

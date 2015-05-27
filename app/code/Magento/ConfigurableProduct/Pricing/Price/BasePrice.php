<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

class BasePrice extends \Magento\Catalog\Pricing\Price\BasePrice
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (!$this->product->getSelectedConfigurableOption()) {
            return parent::getValue();
        } else {
            $childId = $this->product->getSelectedConfigurableOption()->getId();
            if (!isset($this->values[$childId])) {
                $this->value = null;
                $this->values[$childId] = parent::getValue();
            }
            return $this->values[$childId];
        }
    }
}

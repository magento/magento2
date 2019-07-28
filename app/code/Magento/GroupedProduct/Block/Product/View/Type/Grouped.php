<?php
/**
 * Catalog grouped product info block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Product\View\Type;

/**
 * @api
 * @since 100.0.2
 */
class Grouped extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @return array
     */
    public function getAssociatedProducts()
    {
        return $this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct());
    }

    /**
     * Set preconfigured values to grouped associated products
     *
     * @return $this
     */
    public function setPreconfiguredValue()
    {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();
        if (is_array($configValues)) {
            $associatedProducts = $this->getAssociatedProducts();
            foreach ($associatedProducts as $item) {
                if (isset($configValues[$item->getId()])) {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }
}

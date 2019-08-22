<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View\Options\Type;

/**
 * Product options text type block
 *
 * @api
 * @since 100.0.2
 */
class Text extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Returns default value to show in text input
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }
}

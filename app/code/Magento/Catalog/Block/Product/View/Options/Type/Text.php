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
 * @since 2.0.0
 */
class Text extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Returns default value to show in text input
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }
}

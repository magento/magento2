<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml block for fieldset of product custom options
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset;

class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * Get option html block
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     *
     * @return string
     */
    public function getOptionHtml(\Magento\Catalog\Model\Product\Option $option)
    {
        $type = $this->getGroupOfOption($option->getType());
        $renderer = $this->getChildBlock($type);
        $renderer->setSkipJsReloadPrice(1)->setProduct($this->getProduct())->setOption($option);

        return $this->getChildHtml($type, false);
    }
}

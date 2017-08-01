<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Product\Downloads\Renderer;

/**
 * Adminhtml Product Downloads Purchases Renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Purchases extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders Purchases value
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (($value = $row->getData($this->getColumn()->getIndex())) > 0) {
            return $value;
        }
        return __('Unlimited');
    }
}

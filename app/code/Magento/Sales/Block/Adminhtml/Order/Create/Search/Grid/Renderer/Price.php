<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
 * Adminhtml sales create order product search grid price column renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Price
{
    /**
     * Render minimal price for downloadable products
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getTypeId() == 'downloadable') {
            $row->setPrice($row->getPrice());
        }
        return parent::render($row);
    }
}

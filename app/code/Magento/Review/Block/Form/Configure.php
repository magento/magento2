<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Form;

/**
 * Review form block
 *
 * @api
 * @since 100.0.2
 */
class Configure extends \Magento\Review\Block\Form
{
    /**
     * Get review product id
     *
     * @return int
     */
    public function getProductId()
    {
        return (int)$this->getRequest()->getParam('product_id', false);
    }
}

<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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

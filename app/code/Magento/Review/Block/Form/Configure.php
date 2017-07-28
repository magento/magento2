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
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Configure extends \Magento\Review\Block\Form
{
    /**
     * Get review product id
     *
     * @return int
     * @since 2.0.0
     */
    public function getProductId()
    {
        return (int)$this->getRequest()->getParam('product_id', false);
    }
}

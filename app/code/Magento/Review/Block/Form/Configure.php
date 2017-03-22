<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Form;

/**
 * Review form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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

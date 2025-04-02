<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Form;

/**
 * Customer Form Attribute Resource Model
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Form\Attribute
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_form_attribute', 'attribute_id');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Form;

/**
 * Customer Form Attribute Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Form\Attribute
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('customer_form_attribute', 'attribute_id');
    }
}

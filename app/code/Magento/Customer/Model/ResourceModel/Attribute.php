<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel;

/**
 * Class \Magento\Customer\Model\ResourceModel\Attribute
 *
 * @since 2.0.0
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Attribute
{
    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     * @since 2.0.0
     */
    protected function _getEavWebsiteTable()
    {
        return $this->getTable('customer_eav_attribute_website');
    }

    /**
     * Get Form attribute table
     *
     * Get table, where dependency between form name and attribute ids is stored
     *
     * @return string|null
     * @since 2.0.0
     */
    protected function _getFormAttributeTable()
    {
        return $this->getTable('customer_form_attribute');
    }
}

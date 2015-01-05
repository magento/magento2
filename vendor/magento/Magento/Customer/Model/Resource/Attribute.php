<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Customer attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Resource;

class Attribute extends \Magento\Eav\Model\Resource\Attribute
{
    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
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
     */
    protected function _getFormAttributeTable()
    {
        return $this->getTable('customer_form_attribute');
    }
}

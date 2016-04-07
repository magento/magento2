<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Form Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model;

class Form extends \Magento\Eav\Model\Form
{
    /**
     * XML configuration paths for "Disable autocomplete on storefront" property
     */
    const XML_PATH_ENABLE_AUTOCOMPLETE = 'general/restriction/autocomplete_on_storefront';

    /**
     * Current module pathname
     *
     * @var string
     */
    protected $_moduleName = 'Magento_Customer';

    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'customer';

    /**
     * Get EAV Entity Form Attribute Collection for Customer
     * exclude 'created_at'
     *
     * @return \Magento\Customer\Model\ResourceModel\Form\Attribute\Collection
     */
    protected function _getFormAttributeCollection()
    {
        return parent::_getFormAttributeCollection()->addFieldToFilter('attribute_code', ['neq' => 'created_at']);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Form Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model;

/**
 * Class \Magento\Customer\Model\Form
 *
 * @since 2.0.0
 */
class Form extends \Magento\Eav\Model\Form
{
    /**
     * XML configuration paths for "Disable autocomplete on storefront" property
     */
    const XML_PATH_ENABLE_AUTOCOMPLETE = 'customer/password/autocomplete_on_storefront';

    /**
     * Current module pathname
     *
     * @var string
     * @since 2.0.0
     */
    protected $_moduleName = 'Magento_Customer';

    /**
     * Current EAV entity type code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_entityTypeCode = 'customer';

    /**
     * Get EAV Entity Form Attribute Collection for Customer
     * exclude 'created_at'
     *
     * @return \Magento\Customer\Model\ResourceModel\Form\Attribute\Collection
     * @since 2.0.0
     */
    protected function _getFormAttributeCollection()
    {
        return parent::_getFormAttributeCollection()->addFieldToFilter('attribute_code', ['neq' => 'created_at']);
    }
}

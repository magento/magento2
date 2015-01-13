<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return \Magento\Customer\Model\Resource\Form\Attribute\Collection
     */
    protected function _getFormAttributeCollection()
    {
        return parent::_getFormAttributeCollection()->addFieldToFilter('attribute_code', ['neq' => 'created_at']);
    }
}

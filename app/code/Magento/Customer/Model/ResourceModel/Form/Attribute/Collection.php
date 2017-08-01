<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Form\Attribute;

/**
 * Customer Form Attribute Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Form\Attribute\Collection
{
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
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\Eav\Model\Attribute::class, \Magento\Customer\Model\ResourceModel\Form\Attribute::class);
    }

    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored.
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     * @since 2.0.0
     */
    protected function _getEavWebsiteTable()
    {
        return $this->getTable('customer_eav_attribute_website');
    }
}

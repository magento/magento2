<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer EAV additional attribute resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Attribute;

/**
 * Class \Magento\Customer\Model\ResourceModel\Attribute\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_entityTypeCode = 'customer';

    /**
     * Default attribute entity type code
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getEntityTypeCode()
    {
        return $this->_entityTypeCode;
    }

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
}

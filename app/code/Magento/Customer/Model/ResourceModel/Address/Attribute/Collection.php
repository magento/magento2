<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Address EAV additional attribute resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute;

/**
 * Class \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_entityTypeCode = 'customer_address';
}

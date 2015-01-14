<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Address EAV additional attribute resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Resource\Address\Attribute;

class Collection extends \Magento\Customer\Model\Resource\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'customer_address';
}

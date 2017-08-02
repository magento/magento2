<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer Address Form Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\Address;

/**
 * Class \Magento\Customer\Model\Address\Form
 *
 * @since 2.0.0
 */
class Form extends \Magento\Customer\Model\Form
{
    /**
     * Current EAV entity type code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_entityTypeCode = 'customer_address';
}

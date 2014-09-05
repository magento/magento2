<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Service\V1\Data;

/**
 * CustomerGroup Service Data Object
 */
class CustomerGroup extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants for Data Object keys
     */
    const ID = 'id';
    const CODE = 'code';
    const TAX_CLASS_ID = 'tax_class_id';

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Get tax class id
     *
     * @return int
     */
    public function getTaxClassId()
    {
        return $this->_get(self::TAX_CLASS_ID);
    }

    /**
     * Retrieve tax class name
     *
     * @return string|null
     */
    public function getTaxClassName()
    {
        return $this->_get('tax_class_name');
    }
}

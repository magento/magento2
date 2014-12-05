<?php
/**
 *
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

namespace Magento\Customer\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer group interface.
 */
interface GroupInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const ID = 'id';
    const CODE = 'code';
    const TAX_CLASS_ID = 'tax_class_id';
    const TAX_CLASS_NAME = 'tax_class_name';
    const NOT_LOGGED_IN_ID = 0;
    const CUST_GROUP_ALL = 32000;
    const GROUP_CODE_MAX_LENGTH = 32;
    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get tax class id
     *
     * @return int
     */
    public function getTaxClassId();

    /**
     * Get tax class name
     *
     * @return string|null
     */
    public function getTaxClassName();
}

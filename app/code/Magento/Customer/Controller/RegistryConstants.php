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
namespace Magento\Customer\Controller;

/**
 * Declarations of core registry keys used by the Customer module
 *
 */
class RegistryConstants
{
    /**
     * Registry key where current customer DTO stored
     * @todo switch to use ID instead and remove after refactoring of all occurrences
     */
    const CURRENT_CUSTOMER = 'current_customer';

    /**
     * Registry key where current customer ID is stored
     */
    const CURRENT_CUSTOMER_ID = 'current_customer_id';

    /**
     * Registry key where current CustomerGroup ID is stored
     */
    const CURRENT_GROUP_ID = 'current_group_id';
}

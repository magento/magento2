<?php
/**
 * Base service exception
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
namespace Magento\Customer\Service\Entity\V1;

class Exception extends \Exception
{
    /** Error codes */
    const CODE_UNKNOWN                              = 0;
    const CODE_ACCT_ALREADY_ACTIVE                  = 1;
    const CODE_INVALID_RESET_TOKEN                  = 2;
    const CODE_RESET_TOKEN_EXPIRED                  = 3;
    const CODE_EMAIL_NOT_FOUND                      = 4;
    const CODE_CONFIRMATION_NOT_NEEDED              = 5;
    const CODE_CUSTOMER_ID_MISMATCH                 = 6;
    const CODE_EMAIL_NOT_CONFIRMED                  = 7;
    const CODE_INVALID_EMAIL_OR_PASSWORD            = 8;
    const CODE_EMAIL_EXISTS                         = 9;
    const CODE_INVALID_RESET_PASSWORD_LINK_TOKEN    = 10;
    const CODE_ADDRESS_NOT_FOUND                    = 11;
    const CODE_INVALID_ADDRESS_ID                   = 12;
    const CODE_VALIDATION_FAILED                    = 13;
    const CODE_INVALID_CUSTOMER_ID                  = 14;
}

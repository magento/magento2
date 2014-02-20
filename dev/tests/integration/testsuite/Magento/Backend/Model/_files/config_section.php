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
 * @category    Magento
 * @package     Magento_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(array ('section' => 'payment', 'groups' =>array(
// TODO: This piece of code should be uncommented after revert of changes described in MPI-1023 comments
//        'account' => array (
//            'fields' => array (
//                'merchant_country' => array ('value' => 'US'),
//            ),
//        ),
        'paypal_payments' => array(
            'groups' => array(
                'payflow_advanced' => array(
                    'groups' => array(
                        'required_settings' => array(
                            'groups' => array(
                                'payments_advanced' => array(
                                    'fields' => array(
                                        'business_account' => array ('value' => 'owner@example.com')
                                    )
                                )
                            )
                        )
                    )
                ),
                'payflow_link' => array(
                    'groups' => array(
                        'payflow_link_required' => array(
                            'fields' => array(
                                'enable_payflow_link' => array('value' => '1')
                            ),
                            'groups' => array(
                                'payflow_link_payflow_link' => array(
                                    'fields' => array(
                                        'partner' => array ('value' => 'link_partner'),
                                        'vendor' => array ('value' => 'link_vendor'),
                                        'user' => array ('value' => 'link_user'),
                                        'pwd' => array ('value' => 'password'),
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    ),
    'expected' => array(
        'paypal' => array(
            'paypal/general/business_account' => 'owner@example.com',
// TODO: This piece of code should be uncommented after revert of changes described in MPI-1023 comments 
//            'paypal/general/merchant_country' => 'US'
        ),
        'payment/payflow_link' => array(
            'payment/payflow_link/active' => '1',
            'payment/payflow_link/partner' => 'link_partner',
            'payment/payflow_link/vendor' => 'link_vendor',
            'payment/payflow_link/user' => 'link_user',
            'payment/payflow_link/pwd' => 'password',
        )
    )
));

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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>
<?php return array(
    'some_root_timer' => array(
        'start'         => false,
        'sum'           => 0.08,
        'count'         => 2,
        'realmem'       => 50000000,
        'emalloc'       => 51000000,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
    'some_root_timer->some_nested_timer' => array(
        'start'         => false,
        'sum'           => 0.08,
        'count'         => 3,
        'realmem'       => 40000000,
        'emalloc'       => 42000000,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
    'some_root_timer->some_nested_timer->some_deeply_nested_timer' => array(
        'start'         => false,
        'sum'           => 0.03,
        'count'         => 3,
        'realmem'       => 10000000,
        'emalloc'       => 13000000,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
    'one_more_root_timer' => array(
        'start'         => false,
        'sum'           => 0.01,
        'count'         => 1,
        'realmem'       => 12345678,
        'emalloc'       => 23456789,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
);

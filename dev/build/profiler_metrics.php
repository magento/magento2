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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>
<?php return array(
    'test execution time (ms)' =>            array('integration_test'),
    /* Application framework metrics */
    'bootstrap time (ms)' =>                 array('bootstrap'),
    'modules initialization time (ms)' =>    array('init_modules'),
    'request initialization time (ms)' =>    array('init_request'),
    'routing time (ms)' =>                   array(
        'routing_init', 'db_url_rewrite', 'config_url_rewrite', 'routing_match_router'
    ),
    'pre dispatching time (ms)' =>           array('predispatch'),
    'layout overhead time (ms)' =>           array('layout_load', 'layout_generate_xml', 'layout_generate_blocks'),
    'response rendering time (ms)' =>        array('layout_render'),
    'post dispatching time (ms)' =>          array('postdispatch', 'response_send'),
    /* Mage_Catalog module metrics */
    'product save time (ms)' =>              array('catalog_product_save'),
    'product load time (ms)' =>              array('catalog_product_load'),
    'category save time (ms)' =>             array('catalog_category_save'),
    'category load time (ms)' =>             array('catalog_category_load'),
);

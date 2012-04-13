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
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Black list
 * Return table names list which are:
 * a) aliased of previous usage in Zend_Db_Select
 * b) used in dynamic created table names
 * c) not available by used as dead code
 */

return array(
        'c',
        'l',
        'sc',
        'cat_pro',
        'table_name',
        'rule_customer',
        'sales_flat_',
        'catalog_product_link_attribute_',
        'catalog_category_flat_',
        'catalog_category_entity_',
        'catalog_product_flat_',
        'catalog_product_entity_',
        'price_index',
        'invitation',
        'entity_attribute',
        'directory_currency',
        'sales_bestsellers_aggregated_'
    );
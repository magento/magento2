<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Black list
 * Return table names list which are:
 * a) aliased of previous usage in Zend_Db_Select
 * b) used in dynamic created table names
 * c) not available by used as dead code
 */

return [
    'c',
    'l',
    'sc',
    'cat_pro',
    'table_name',
    'rule_customer',
    'sales_',
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
];

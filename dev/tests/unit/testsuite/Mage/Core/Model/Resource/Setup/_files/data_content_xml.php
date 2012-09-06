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
 * @category    Mage
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    '$replaceRules' => array(
        array(
            'table',
            'field',
            Mage_Core_Model_Resource_Setup_Migration::ENTITY_TYPE_BLOCK,
            Mage_Core_Model_Resource_Setup_Migration::FIELD_CONTENT_TYPE_XML,
        )
    ),
    '$tableData' => array(
        array('field' => '<reference><block type="catalog/product_new" /></reference>'),
        array('field' => '<reference><block type="catalogSearch/result" /></reference>'),
        array('field' => '<reference></reference>'),
    ),
    '$expected' => array(
        'updates' => array(
            array(
                'table' => 'table',
                'field' => 'field',
                'to'    => '<reference><block type="Mage_Catalog_Block_Product_New" /></reference>',
                'from'  => array('`field` = ?' => '<reference><block type="catalog/product_new" /></reference>')
            ),
            array(
                'table' => 'table',
                'field' => 'field',
                'to'    => '<reference><block type="Mage_CatalogSearch_Block_Result" /></reference>',
                'from'  => array('`field` = ?' => '<reference><block type="catalogSearch/result" /></reference>')
            ),
        ),
        'aliases_map' => array(
            Mage_Core_Model_Resource_Setup_Migration::ENTITY_TYPE_BLOCK => array(
                'catalog/product_new'  => 'Mage_Catalog_Block_Product_New',
                'catalogSearch/result' => 'Mage_CatalogSearch_Block_Result',
            )
        )
    ),
);

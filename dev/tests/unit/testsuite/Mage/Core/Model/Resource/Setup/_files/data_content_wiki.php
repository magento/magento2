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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    '$replaceRules' => array(
        array(
            'table',
            'field',
            Mage_Core_Model_Resource_Setup_Migration::ENTITY_TYPE_BLOCK,
            Mage_Core_Model_Resource_Setup_Migration::FIELD_CONTENT_TYPE_WIKI
        )
    ),
    '$tableData' => array(
        array('field' => '<p>{{widget type="productalert/product_view"}}</p>'),
        array('field' => '<p>{{widget type="catalogSearch/result"}}</p>'),
        array('field' => '<p>Some HTML code</p>'),
    ),
    '$expected' => array(
        'updates' => array(
            array(
                'table' => 'table',
                'field' => 'field',
                'to'    => '<p>{{widget type="Mage_ProductAlert_Block_Product_View"}}</p>',
                'from'  => array('`field` = ?' => '<p>{{widget type="productalert/product_view"}}</p>')
            ),
            array(
                'table' => 'table',
                'field' => 'field',
                'to'    => '<p>{{widget type="Mage_CatalogSearch_Block_Result"}}</p>',
                'from'  => array('`field` = ?' => '<p>{{widget type="catalogSearch/result"}}</p>')
            ),
        ),
        'aliases_map' => array(
            Mage_Core_Model_Resource_Setup_Migration::ENTITY_TYPE_BLOCK => array(
                'productalert/product_view' => 'Mage_ProductAlert_Block_Product_View',
                'catalogSearch/result'      => 'Mage_CatalogSearch_Block_Result',
            )
        )
    ),
);

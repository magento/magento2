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
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Dataflow profile data
 */
$dataflowData = array(
    array(
        'name'         => 'Export All Products',
        'actions_xml'  => '<action type="Mage_Catalog_Model_Convert_Adapter_Product" method="load">\r\n    <var name="store"><![CDATA[0]]></var>\r\n</action>\r\n\r\n<action type="Mage_Catalog_Model_Convert_Parser_Product" method="unparse">\r\n    <var name="store"><![CDATA[0]]></var>\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Mapper_Column" method="map">\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Parser_Csv" method="unparse">\r\n    <var name="delimiter"><![CDATA[,]]></var>\r\n    <var name="enclose"><![CDATA["]]></var>\r\n    <var name="fieldnames">true</var>\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Adapter_Io" method="save">\r\n    <var name="type">file</var>\r\n    <var name="path">var/export</var>\r\n    <var name="filename"><![CDATA[export_all_products.csv]]></var>\r\n</action>\r\n\r\n',
        'gui_data'     => 'a:5:{s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:23:"export_all_products.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}',
        'direction'    => 'export',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'file'
    ),
    array(
        'name'         => 'Export Product Stocks',
        'actions_xml'  => '<action type="Mage_Catalog_Model_Convert_Adapter_Product" method="load">\r\n    <var name="store"><![CDATA[0]]></var>\r\n</action>\r\n\r\n<action type="Mage_Catalog_Model_Convert_Parser_Product" method="unparse">\r\n    <var name="store"><![CDATA[0]]></var>\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Mapper_Column" method="map">\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Parser_Csv" method="unparse">\r\n    <var name="delimiter"><![CDATA[,]]></var>\r\n    <var name="enclose"><![CDATA["]]></var>\r\n    <var name="fieldnames">true</var>\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Adapter_Io" method="save">\r\n    <var name="type">file</var>\r\n    <var name="path">var/export</var>\r\n    <var name="filename"><![CDATA[export_all_products.csv]]></var>\r\n</action>\r\n\r\n',
        'gui_data'     => 'a:5:{s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:25:"export_product_stocks.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:4:"true";s:7:"product";a:2:{s:2:"db";a:4:{i:1;s:5:"store";i:2;s:3:"sku";i:3;s:3:"qty";i:4;s:11:"is_in_stock";}s:4:"file";a:4:{i:1;s:5:"store";i:2;s:3:"sku";i:3;s:3:"qty";i:4;s:11:"is_in_stock";}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}',
        'direction'    => 'export',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'file'
    ),
    array(
        'name'         => 'Import All Products',
        'actions_xml'  => '<action type="Mage_Dataflow_Model_Convert_Parser_Csv" method="parse">\r\n    <var name="delimiter"><![CDATA[,]]></var>\r\n    <var name="enclose"><![CDATA["]]></var>\r\n    <var name="fieldnames">true</var>\r\n    <var name="store"><![CDATA[0]]></var>\r\n    <var name="adapter">Mage_Catalog_Model_Convert_Adapter_Product</var>\r\n    <var name="method">parse</var>\r\n</action>',
        'gui_data'     => 'a:5:{s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:23:"export_all_products.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}',
        'direction'    => 'import',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'interactive'
    ),
    array(
        'name'         => 'Import Product Stocks',
        'actions_xml'  => '<action type="Mage_Dataflow_Model_Convert_Parser_Csv" method="parse">\r\n    <var name="delimiter"><![CDATA[,]]></var>\r\n    <var name="enclose"><![CDATA["]]></var>\r\n    <var name="fieldnames">true</var>\r\n    <var name="store"><![CDATA[0]]></var>\r\n    <var name="adapter">Mage_Catalog_Model_Convert_Adapter_Product</var>\r\n    <var name="method">parse</var>\r\n</action>',
        'gui_data'     => 'a:5:{s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:18:"export_product.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}',
        'direction'    => 'import',
        'entity_type'  => 'product',
        'store_id'     => 0,
        'data_transfer'=> 'interactive'
    ),
    array(
        'name'         => 'Export Customers',
        'actions_xml'  => '<action type="Mage_Customer_Model_Convert_Adapter_Customer" method="load">\r\n    <var name="store"><![CDATA[0]]></var>\r\n    <var name="filter/adressType"><![CDATA[default_billing]]></var>\r\n</action>\r\n\r\n<action type="Mage_Customer_Model_Convert_Parser_Customer" method="unparse">\r\n    <var name="store"><![CDATA[0]]></var>\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Mapper_Column" method="map">\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Parser_Csv" method="unparse">\r\n    <var name="delimiter"><![CDATA[,]]></var>\r\n    <var name="enclose"><![CDATA["]]></var>\r\n    <var name="fieldnames">true</var>\r\n</action>\r\n\r\n<action type="Mage_Dataflow_Model_Convert_Adapter_Io" method="save">\r\n    <var name="type">file</var>\r\n    <var name="path">var/export</var>\r\n    <var name="filename"><![CDATA[export_customers.csv]]></var>\r\n</action>\r\n\r\n',
        'gui_data'     => 'a:5:{s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:20:"export_customers.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}',
        'direction'    => 'export',
        'entity_type'  => 'customer',
        'store_id'     => 0,
        'data_transfer'=> 'file'
    ),
    array(
        'name'         => 'Import Customers',
        'actions_xml'  => '<action type="Mage_Dataflow_Model_Convert_Parser_Csv" method="parse">\r\n    <var name="delimiter"><![CDATA[,]]></var>\r\n    <var name="enclose"><![CDATA["]]></var>\r\n    <var name="fieldnames">true</var>\r\n    <var name="store"><![CDATA[0]]></var>\r\n    <var name="adapter">Mage_Customer_Model_Convert_Adapter_Customer</var>\r\n    <var name="method">parse</var>\r\n</action>',
        'gui_data'     => 'a:5:{s:4:"file";a:7:{s:4:"type";s:4:"file";s:8:"filename";s:19:"export_customer.csv";s:4:"path";s:10:"var/export";s:4:"host";s:0:"";s:4:"user";s:0:"";s:8:"password";s:0:"";s:7:"passive";s:0:"";}s:5:"parse";a:5:{s:4:"type";s:3:"csv";s:12:"single_sheet";s:0:"";s:9:"delimiter";s:1:",";s:7:"enclose";s:1:""";s:10:"fieldnames";s:4:"true";}s:3:"map";a:3:{s:14:"only_specified";s:0:"";s:7:"product";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}s:8:"customer";a:2:{s:2:"db";a:0:{}s:4:"file";a:0:{}}}s:7:"product";a:1:{s:6:"filter";a:8:{s:4:"name";s:0:"";s:3:"sku";s:0:"";s:4:"type";s:1:"0";s:13:"attribute_set";s:0:"";s:5:"price";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:3:"qty";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}s:10:"visibility";s:1:"0";s:6:"status";s:1:"0";}}s:8:"customer";a:1:{s:6:"filter";a:10:{s:9:"firstname";s:0:"";s:8:"lastname";s:0:"";s:5:"email";s:0:"";s:5:"group";s:1:"0";s:10:"adressType";s:15:"default_billing";s:9:"telephone";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:6:"region";s:0:"";s:10:"created_at";a:2:{s:4:"from";s:0:"";s:2:"to";s:0:"";}}}}',
        'direction'    => 'import',
        'entity_type'  => 'customer',
        'store_id'     => 0,
        'data_transfer'=> 'interactive'
    ),
);

foreach ($dataflowData as $bind) {
    Mage::getModel('Mage_Dataflow_Model_Profile')->setData($bind)->save();
}

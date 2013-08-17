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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$fixture = simplexml_load_file(__DIR__ . '/_data/xml/LinkCRUD.xml');

//Create new downloadable product
$productData = Magento_Test_Helper_Api::simpleXmlToArray($fixture->product);
$productData['sku'] = $productData['sku'] . mt_rand(1000, 9999);
$productData['name'] = $productData['name'] . ' ' . mt_rand(1000, 9999);
$linksData = array(
    array(
        'title' => 'Test Link 1',
        'price' => '1',
        'is_unlimited' => '1',
        'number_of_downloads' => '0',
        'is_shareable' => '0',
        'sample' => array(
            'type' => 'url',
            'url' => 'http://www.magentocommerce.com/img/logo.gif',
        ),
        'type' => 'url',
        'link_url' => 'http://www.magentocommerce.com/img/logo.gif',
    ),
    array(
        'title' => 'Test Link 2',
        'price' => '2',
        'is_unlimited' => '0',
        'number_of_downloads' => '10',
        'is_shareable' => '1',
        'sample' =>
        array(
            'type' => 'url',
            'url' => 'http://www.magentocommerce.com/img/logo.gif',
        ),
        'type' => 'url',
        'link_url' => 'http://www.magentocommerce.com/img/logo.gif',
    ),
);

$product = Mage::getModel('Mage_Catalog_Model_Product');
$product->setData($productData)
    ->setStoreId(0)
    ->setDownloadableData(array('link' => $linksData))
    ->save();
Mage::register('downloadable', $product);

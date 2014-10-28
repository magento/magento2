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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
/** @var $category \Magento\Catalog\Model\Category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
$category->setId(
    3
)->setName(
    'Category 1'
)->setParentId(
    2
)->setPath(
    '1/2/3'
)->setLevel(
    2
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

$urlKeys = array('url-key', 'url-key-1', 'url-key-2', 'url-key-5', 'url-key-1000', 'url-key-999', 'url-key-asdf');

foreach ($urlKeys as $i => $urlKey) {
    $id = $i + 1;
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $product->setTypeId(
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
    )->setId(
        $id
    )->setStoreId(
        1
    )->setAttributeSetId(
        4
    )->setWebsiteIds(
        array(1)
    )->setName(
        'Simple Product ' . $id
    )->setSku(
        'simple-' . $id
    )->setPrice(
        10
    )->setCategoryIds(
        array(3)
    )->setVisibility(
        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
    )->setStatus(
        \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
    )->setUrlKey(
        $urlKey
    )->setUrlPath(
        $urlKey //. '.html'
    )->save();
}

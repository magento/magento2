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
/** @var \Magento\ToolkitFramework\Application $this */
$categoriesNumber = \Magento\ToolkitFramework\Config::getInstance()->getValue('categories', 18);
$maxNestingLevel = \Magento\ToolkitFramework\Config::getInstance()->getValue('categories_nesting_level', 3);
$this->resetObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $this->getObjectManager()->create('\Magento\Store\Model\StoreManager');
/** @var $category \Magento\Catalog\Model\Category */
$category = $this->getObjectManager()->create('Magento\Catalog\Model\Category');

$groups = array();
$storeGroups = $storeManager->getGroups();
$i = 0;
foreach ($storeGroups as $storeGroup) {
    $parentCategoryId[$i] = $defaultParentCategoryId[$i] = $storeGroup->getRootCategoryId();
    $nestingLevel[$i] = 1;
    $nestingPath[$i] = "1/$parentCategoryId[$i]";
    $categoryPath[$i] = '';
    $i++;
}
$groupNumber = 0;
$anchorStep = 2;
$categoryIndex = 1;

while ($categoryIndex <= $categoriesNumber) {
    $category->setId(null)
        ->setUrlKey(null)
        ->setUrlPath(null)
        ->setName("Category $categoryIndex")
        ->setParentId($parentCategoryId[$groupNumber])
        ->setPath($nestingPath[$groupNumber])
        ->setLevel($nestingLevel[$groupNumber])
        ->setAvailableSortBy('name')
        ->setDefaultSortBy('name')
        ->setIsActive(true)
        //->setIsAnchor($categoryIndex++ % $anchorStep == 0)
        ->save();
    $categoryIndex++;
    $categoryPath[$groupNumber] .=  '/' . $category->getName();

    if ($nestingLevel[$groupNumber]++ == $maxNestingLevel) {
        $nestingLevel[$groupNumber] = 1;
        $parentCategoryId[$groupNumber] = $defaultParentCategoryId[$groupNumber];
        $nestingPath[$groupNumber] = '1';
        $categoryPath[$groupNumber] = '';
    } else {
        $parentCategoryId[$groupNumber] = $category->getId();
    }
    $nestingPath[$groupNumber] .= "/$parentCategoryId[$groupNumber]";

    $groupNumber++;
    if ($groupNumber==count($defaultParentCategoryId)) {
        $groupNumber = 0;
    }
}

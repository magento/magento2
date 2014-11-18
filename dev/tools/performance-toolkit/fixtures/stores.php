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
$websitesCount = \Magento\ToolkitFramework\Config::getInstance()->getValue('websites', 2);
$storeGroupsCount = \Magento\ToolkitFramework\Config::getInstance()->getValue('store_groups', 3);
$storesCount = \Magento\ToolkitFramework\Config::getInstance()->getValue('store_views', 5);
$this->resetObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $this->getObjectManager()->create('\Magento\Store\Model\StoreManager');
/** @var $category \Magento\Catalog\Model\Category */
$category = $this->getObjectManager()->create('Magento\Catalog\Model\Category');

/** @var $defaultWebsite \Magento\Store\Model\Website */
$defaultWebsite = $storeManager->getWebsite();
/** @var $defaultStoreGroup \Magento\Store\Model\Group */
$defaultStoreGroup = $storeManager->getGroup();
/** @var $defaultStoreView \Magento\Store\Model\Store */
$defaultStoreView = $storeManager->getDefaultStoreView();

$defaultParentCategoryId =  $storeManager->getStore()->getRootCategoryId();

$defaultWebsiteId = $defaultWebsite->getId();
$defaultStoreGroupId = $defaultStoreGroup->getId();
$defaultStoreViewId = $defaultStoreView->getId();

$websitesId = array();
$groupsId = array();

//Create $websitesCount websites
for ($i = 0; $i < $websitesCount; $i++) {
    $websiteId = null;
    if ($i == 0) {
        $websiteId = $defaultWebsiteId;
    }
    $website = clone $defaultWebsite;
    $websiteCode = sprintf('website_%d', $i + 1);
    $websiteName = sprintf('Website %d', $i + 1);
    $website->addData(
        array(
            'website_id'    => $websiteId,
            'code'          => $websiteCode,
            'name'          => $websiteName,
            'is_default'    => (int)$i == 0,
        )
    );
    $website->save();
    $websitesId[$i] = $website->getId();
    usleep(20);
}

//Create $storeGroupsCount websites
$websiteNumber = 0;
for ($i = 0; $i < $storeGroupsCount; $i++) {
    $websiteId = $websitesId[$websiteNumber];
    $groupId = null;
    $parentCategoryId = null;
    $categoryPath = '1';

    $storeGroupName = sprintf('Store Group %d - website_id_%d', $i + 1, $websiteId);

    if ($i == 0 && $websiteId == $defaultWebsiteId) {
        $groupId = $defaultStoreGroupId;
        $parentCategoryId = $defaultParentCategoryId;
        $categoryPath = '1/' . $defaultParentCategoryId;
    }

    $category->setId($parentCategoryId)
        ->setName("Category $storeGroupName")
        ->setPath($categoryPath)
        ->setLevel(1)
        ->setAvailableSortBy('name')
        ->setDefaultSortBy('name')
        ->setIsActive(true)
        ->save();

    $storeGroup = clone $defaultStoreGroup;
    $storeGroup->addData(
        array(
            'group_id'          => $groupId,
            'website_id'        => $websiteId,
            'name'              => $storeGroupName,
            'root_category_id'  => $category->getId()
        )
    );
    $storeGroup->save();
    $groupsId[$websiteId][] = $storeGroup->getId();

    $websiteNumber++;
    if ($websiteNumber==count($websitesId)) {
        $websiteNumber = 0;
    }
    usleep(20);
}

//Create $storesCount stores
$websiteNumber = 0;
$groupNumber = 0;
for ($i = 0; $i < $storesCount; $i++) {
    $websiteId = $websitesId[$websiteNumber];
    $groupId = $groupsId[$websiteId][$groupNumber];
    $storeId = null;
    if ($i == 0 && $groupId == $defaultStoreGroupId) {
        $storeId = $defaultStoreViewId;
    }
    $store = clone $defaultStoreView;
    $storeCode = sprintf('store_view_%d_w_%d_g_%d', $i + 1, $websiteId, $groupId);
    $storeName = sprintf('Store view %d - website_id_%d - group_id_%d', $i + 1, $websiteId, $groupId);
    $store->addData(
        array(
            'store_id'      => $storeId,
            'name'          => $storeName,
            'website_id'    => $websiteId,
            'group_id'      => $groupId
        )
    );

    if ($storeId == null) {
        $store->addData(
            array(
                'code' => $storeCode
            )
        );
    }

    $store->save();

    $groupNumber++;
    if ($groupNumber==count($groupsId[$websiteId])) {
        $groupNumber = 0;
        $websiteNumber++;
        if ($websiteNumber==count($websitesId)) {
            $websiteNumber = 0;
        }
    }
    usleep(20);
}

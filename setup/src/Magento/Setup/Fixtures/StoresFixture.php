<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class StoresFixture
 */
class StoresFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 10;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $websitesCount = $this->fixtureModel->getValue('websites', 0);
        $storeGroupsCount = $this->fixtureModel->getValue('store_groups', 0);
        $storesCount = $this->fixtureModel->getValue('store_views', 0);
        if (!$websitesCount || !$storeGroupsCount || !$storesCount) {
            return;
        }
        $this->fixtureModel->resetObjectManager();

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->create('Magento\Catalog\Model\Category');

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

        $websitesId = [];
        $groupsId = [];

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
                [
                    'website_id'    => $websiteId,
                    'code'          => $websiteCode,
                    'name'          => $websiteName,
                    'is_default'    => (int)$i == 0,
                ]
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
                [
                    'group_id'          => $groupId,
                    'website_id'        => $websiteId,
                    'name'              => $storeGroupName,
                    'root_category_id'  => $category->getId(),
                ]
            );
            $storeGroup->save();
            $groupsId[$websiteId][] = $storeGroup->getId();

            $websiteNumber++;
            if ($websiteNumber == count($websitesId)) {
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
                [
                    'store_id'      => $storeId,
                    'name'          => $storeName,
                    'website_id'    => $websiteId,
                    'group_id'      => $groupId,
                ]
            );

            if ($storeId == null) {
                $store->addData(
                    [
                        'code' => $storeCode,
                    ]
                );
            }

            $store->save();

            $groupNumber++;
            if ($groupNumber == count($groupsId[$websiteId])) {
                $groupNumber = 0;
                $websiteNumber++;
                if ($websiteNumber == count($websitesId)) {
                    $websiteNumber = 0;
                }
            }
            usleep(20);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating websites, stores and store views';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'websites'     => 'Websites',
            'store_groups' => 'Store Groups',
            'store_views'  => 'Store Views'
        ];
    }
}

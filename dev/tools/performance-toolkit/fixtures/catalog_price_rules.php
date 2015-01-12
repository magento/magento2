<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\ToolkitFramework\Application $this */
$catalogPriceRulesCount = \Magento\ToolkitFramework\Config::getInstance()->getValue('catalog_price_rules', 3);
$this->resetObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $this->getObjectManager()->create('Magento\Store\Model\StoreManager');
/** @var $category \Magento\Catalog\Model\Category */
$category = $this->getObjectManager()->get('Magento\Catalog\Model\Category');
/** @var $model  \Magento\CatalogRule\Model\Rule*/
$model = $this->getObjectManager()->get('Magento\CatalogRule\Model\Rule');
//Get all websites
$categoriesArray = [];
$websites = $storeManager->getWebsites();
foreach ($websites as $website) {
    //Get all groups
    $websiteGroups = $website->getGroups();
    foreach ($websiteGroups as $websiteGroup) {
        $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
        $category->load($websiteGroupRootCategory);
        $categoryResource = $category->getResource();
        //Get all categories
        $resultsCategories = $categoryResource->getAllChildren($category);
        foreach ($resultsCategories as $resultsCategory) {
            $category->load($resultsCategory);
            $structure = explode('/', $category->getPath());
            if (count($structure) > 2) {
                $categoriesArray[] = [$category->getId(), $website->getId()];
            }
        }
    }
}
asort($categoriesArray);
$categoriesArray = array_values($categoriesArray);
$idField = $model->getIdFieldName();

for ($i = 0; $i < $catalogPriceRulesCount; $i++) {
    $ruleName = sprintf('Catalog Price Rule %1$d', $i);
    $data = [
        $idField                => null,
        'name'                  => $ruleName,
        'description'           => '',
        'is_active'             => '1',
        'website_ids'           => $categoriesArray[$i % count($categoriesArray)][1],
        'customer_group_ids'    => [
            0 => '0',
            1 => '1',
            2 => '2',
            3 => '3',
        ],
        'from_date'             => '',
        'to_date'               => '',
        'sort_order'            => '',
        'rule'                  => [
            'conditions' => [
                1 => [
                    'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product',
                    'attribute' => 'category_ids',
                    'operator' => '==',
                    'value' => $categoriesArray[$i % count($categoriesArray)][0],
                ],
            ],
        ],
        'simple_action'             => 'by_percent',
        'discount_amount'           => '15',
        'sub_is_enable'              => '0',
        'sub_simple_action'             => 'by_percent',
        'sub_discount_amount'         => '0',
        'stop_rules_processing'      => '0',
        'page'                      => '1',
        'limit'                     => '20',
        'in_banners'                => '1',
        'banner_id'                 => [
            'from'  => '',
            'to'    => '',
        ],
        'banner_name'               => '',
        'visible_in'                => '',
        'banner_is_enabled'         => '',
        'related_banners'           => [],
    ];
    if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
        && isset($data['discount_amount'])
    ) {
        $data['discount_amount'] = min(100, $data['discount_amount']);
    }
    if (isset($data['rule']['conditions'])) {
        $data['conditions'] = $data['rule']['conditions'];
    }
    if (isset($data['rule']['actions'])) {
        $data['actions'] = $data['rule']['actions'];
    }
    unset($data['rule']);

    $model->loadPost($data);
    $useAutoGeneration = (int)!empty($data['use_auto_generation']);
    $model->setUseAutoGeneration($useAutoGeneration);
    $model->save();
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Products > Catalog.
 * 3. Start to create simple product.
 * 4. Fill in data according to data set.
 * 5. Save Product.
 * 6. Perform appropriate assertions.
 *
 * @ZephyrId MAGETWO-27238
 */
class CreateProductWithSeveralWebsitesUrlRewriteTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Run create product with several websites url rewrite test.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param FixtureFactory $fixtureFactory
     * @param array $websiteCategories
     * @return array
     */
    public function testCreate(
        CatalogProductSimple $product,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        FixtureFactory $fixtureFactory,
        array $websiteCategories
    ) {
        $categoryParent = [];
        $categoryList = [];
        $storeList = [];

        // Preconditions
        foreach ($websiteCategories as $websiteCategory) {
            list($storeGroup, $store, $category) = explode('::', $websiteCategory);
            if (!isset($categoryParent[$category])) {
                $categoryListItem = $fixtureFactory->createByCode('category', ['dataset' => $category]);
                $categoryListItem->persist();
                $categoryParent[$category] = $categoryListItem->getDataFieldConfig('parent_id')['source']
                    ->getParentCategory();
                $categoryList[] = $categoryListItem;
            }
            $storeGroup = $fixtureFactory->createByCode('storeGroup', [
                'dataset' => $storeGroup,
                'data' => [
                    'root_category_id' => [
                        'category' => $categoryParent[$category]
                    ]
                ]
            ]);
            $storeGroup->persist();
            $store = $fixtureFactory->createByCode('store', [
                'dataset' => $store,
                'data' => [
                    'group_id' => [
                        'storeGroup' => $storeGroup
                    ]
                ]
            ]);
            $store->persist();
            $storeList[] = $store;
        }

        $productData = $product->getData();
        $productData['website_ids'] = $storeList;
        $productData['category_ids'] = $categoryList;

        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'default',
                'data' => $productData,
            ]
        );

        // Steps
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $newProductPage->getProductForm()->fill($product);
        $newProductPage->getFormPageActions()->save();

        return ['product' => $product];
    }
}

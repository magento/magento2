<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class CategoriesFixture
 */
class CategoriesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 20;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $categoriesNumber = $this->fixtureModel->getValue('categories', 0);
        if (!$categoriesNumber) {
            return;
        }
        $maxNestingLevel = $this->fixtureModel->getValue('categories_nesting_level', 3);
        $this->fixtureModel->resetObjectManager();

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->create('Magento\Catalog\Model\Category');

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
        $categoryIndex = 1;

        while ($categoryIndex <= $categoriesNumber) {
            $category->setId(null)
                ->setUrlKey(null)
                ->setUrlPath(null)
                ->setName("Category $categoryIndex")
                ->setParentId($parentCategoryId[$groupNumber])
                ->setPath($nestingPath[$groupNumber])
                ->setLevel($nestingLevel[$groupNumber] + 1)
                ->setAvailableSortBy('name')
                ->setIsAnchor(false)
                ->setDefaultSortBy('name')
                ->setIsActive(true)
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
            if ($groupNumber == count($defaultParentCategoryId)) {
                $groupNumber = 0;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating categories';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'categories' => 'Categories'
        ];
    }
}

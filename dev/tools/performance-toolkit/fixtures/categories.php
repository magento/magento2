<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class CategoriesFixture
 */
class CategoriesFixture extends \Magento\ToolkitFramework\Fixture
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
        $categoriesNumber = \Magento\ToolkitFramework\Config::getInstance()->getValue('categories', 18);
        $maxNestingLevel = \Magento\ToolkitFramework\Config::getInstance()->getValue('categories_nesting_level', 3);
        $this->application->resetObjectManager();

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->application->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->application->getObjectManager()->create('Magento\Catalog\Model\Category');

        $groups = [];
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

return new CategoriesFixture($this);

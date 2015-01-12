<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

/**
 * Class Categories Helper
 *
 */
class Categories
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * Get categories
     *
     * @var array
     */
    protected $_categories = [];

    /**
     * Categories count
     *
     * @var int
     */
    protected $_categoriesNumber = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $rootCategoryId = $this->getObjectManager()->create(
            'Magento\Store\Model\StoreManager'
        )->getDefaultStoreView()->getRootCategoryId();

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->getObjectManager()->get('Magento\Catalog\Model\Category');
        $category->load($rootCategoryId);

        /** @var $categoryResource \Magento\Catalog\Model\Resource\Category */
        $categoryResource = $category->getResource();
        $categories = $categoryResource->getAllChildren($category);
        $this->_categoriesNumber = count($categories);

        /**
         * Preapre categories paths for import
         *
         * @see \Magento\CatalogImportExport\Model\Import\Product::_initCategories()
         */
        foreach ($categories as $key => $categoryId) {
            $category->load($categoryId);
            $structure = explode('/', $category->getPath());
            $pathSize = count($structure);
            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $category->load($structure[$i])->getName();
                }
                array_shift($path);
                $categories[$key] = implode('/', $path);
            } else {
                $categories[$key] = $category->getName();
            }
        }

        /** Removing store root categories */
        $this->_categories = array_values(array_filter($categories));
        $this->_categoriesNumber = count($this->_categories);
    }

    /**
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManager\ObjectManager|null
     */
    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
            $this->_objectManager = $objectManagerFactory->create($_SERVER);
        }
        return $this->_objectManager;
    }

    /**
     * Get for import number by increment
     *
     * @param $index
     *
     * @return mixed
     */
    public function getCategoryForImport($index)
    {
        return $this->_categories[$index % $this->_categoriesNumber];
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

class CategoryProcessor
{
    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $categoryColFactory;

    /**
     * Categories text-path to ID hash.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * Categories text-path to ID hash with roots checking.
     *
     * @var array
     */
    protected $categoriesWithRoots = [];

    /**
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryColFactory
    ) {
        $this->categoryColFactory = $categoryColFactory;
    }

    /**
     * @return $this
     */
    protected function initCategories()
    {
        if (empty($this->categories) && empty($this->categoriesWithRoots)) {
            $collection = $this->categoryColFactory->create()->addNameToResult();
            /* @var $collection \Magento\Catalog\Model\Resource\Category\Collection */
            foreach ($collection as $category) {
                $structure = explode('/', $category->getPath());
                $pathSize = count($structure);
                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $path[] = $collection->getItemById($structure[$i])->getName();
                    }
                    $rootCategoryName = array_shift($path);
                    if (!isset($this->categoriesWithRoots[$rootCategoryName])) {
                        $this->categoriesWithRoots[$rootCategoryName] = [];
                    }
                    $index = implode('/', $path);
                    $this->categoriesWithRoots[$rootCategoryName][$index] = $category->getId();
                    if ($pathSize > 2) {
                        $this->categories[$index] = $category->getId();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param string $root
     * @param null|string $index
     * @return mixed
     */
    public function getCategoryWithRoot($root, $index = null)
    {
        $this->initCategories();
        $returnVal = isset($this->categoriesWithRoots[$root]) ? $this->categoriesWithRoots[$root] : null;
        if (empty($returnVal) || $index === null) {
            return $returnVal;
        }
        return isset($returnVal[$index]) ? $returnVal[$index] : null;
    }

    /**
     * @param string $index
     * @return null|string
     */
    public function getCategory($index)
    {
        $this->initCategories();
        return isset($this->categories[$index]) ? $this->categories[$index] : null;
    }
}

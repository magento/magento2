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
    protected $_categories = array();

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
                $path = array();
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
            $locatorFactory = new \Magento\Framework\App\ObjectManagerFactory();
            $this->_objectManager = $locatorFactory->create(BP, $_SERVER);
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

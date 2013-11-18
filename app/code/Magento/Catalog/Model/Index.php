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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Category/Product Index
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model;

class Index
{
    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\Resource\Category
     */
    protected $_catalogCategory;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $_catalogProduct;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Resource\Product $catalogProduct
     * @param \Magento\Catalog\Model\Resource\Category $catalogCategory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product $catalogProduct,
        \Magento\Catalog\Model\Resource\Category $catalogCategory,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_catalogCategory = $catalogCategory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Rebuild indexes
     *
     * @return \Magento\Catalog\Model\Index
     */
    public function rebuild()
    {
        $this->_catalogCategory->refreshProductIndex();
        foreach ($this->_storeManager->getStores() as $store) {
            $this->_catalogProduct->refreshEnabledIndex($store);
        }
        return $this;
    }
}

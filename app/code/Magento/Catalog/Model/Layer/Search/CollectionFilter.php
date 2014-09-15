<?php
/**
 *
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
namespace Magento\Catalog\Model\Layer\Search;

use Magento\Catalog\Model\Layer\CollectionFilterInterface;

class CollectionFilter implements CollectionFilterInterface
{
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\CatalogSearch\Helper\Data $helper
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     */
    public function __construct(
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\CatalogSearch\Helper\Data $helper,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->productVisibility = $productVisibility;
    }

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filter(
        $collection,
        \Magento\Catalog\Model\Category $category
    ) {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addSearchFilter($this->helper->getQuery()->getQueryText())
            ->setStore($this->storeManager->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());
    }
}

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
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

class Helper
{
    /**
     * @var \Magento\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockDataFilter
     */
    protected $stockFilter;

    /**
     * @var Helper\ProductLinks
     */
    protected $productLinks;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param Helper\ProductLinks $productLinks
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        Helper\ProductLinks $productLinks
    ) {
        $this->request = $request;
        $this->jsHelper = $jsHelper;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
    }

    /**
     * Initialize product before saving
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function initialize(\Magento\Catalog\Model\Product $product)
    {
        $productData = $this->request->getPost('product');

        if ($productData) {
            $stockData = isset($productData['stock_data']) ? $productData['stock_data'] : array();
            $productData['stock_data'] = $this->stockFilter->filter($stockData);
        }

        foreach (array('category_ids', 'website_ids') as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = array();
            }
        }

        $wasLockedMedia = false;
        if ($product->isLockedAttribute('media')) {
            $product->unlockAttribute('media');
            $wasLockedMedia = true;
        }

        $product->addData($productData);

        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }

        if ($this->storeManager->hasSingleStore()) {
            $product->setWebsiteIds(array($this->storeManager->getStore(true)->getWebsite()->getId()));
        }

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($product->getId() && isset($productData['url_key_create_redirect'])) {
            $product->setData('save_rewrites_history', (bool)$productData['url_key_create_redirect']);
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        $useDefaults = $this->request->getPost('use_default');
        if ($useDefaults) {
            foreach ($useDefaults as $attributeCode) {
                $product->setData($attributeCode, false);
            }
        }

        $product = $this->productLinks->initializeLinks($product);

        /**
         * Initialize product options
         */
        if (isset($productData['options']) && !$product->getOptionsReadonly()) {
            $product->setProductOptions($productData['options']);
        }

        $product->setCanSaveCustomOptions(
            (bool)$this->request->getPost('affect_product_custom_options') && !$product->getOptionsReadonly()
        );

        return $product;
    }
}

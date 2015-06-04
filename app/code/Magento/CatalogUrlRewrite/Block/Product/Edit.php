<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Block\Product;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\CatalogUrlRewrite\Model\Mode\Category as CategoryMode;
use Magento\CatalogUrlRewrite\Model\Mode\Product as ProductMode;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
/**
 * Block for Catalog Category URL rewrites editing
 */
/**
 * @method \Magento\UrlRewrite\Model\UrlRewrite getUrlRewrite()
 * @method \Magento\Catalog\Model\Product getProduct()
 * @method Edit setProduct(\Magento\Catalog\Model\Product $product)
 * @method \Magento\Catalog\Model\Category getCategory()
 * @method Edit setCategory(\Magento\Catalog\Model\Category $category)
 */
class Edit extends \Magento\UrlRewrite\Block\Edit
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\Mode\Product
     */
    protected $productMode;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Mode\Category
     */
    protected $categoryMode;

    /**
     * @param Context $context
     * @param UrlRewriteFactory $rewriteFactory
     * @param BackendHelper $adminhtmlData
     * @param ProductMode $productMode
     * @param CategoryMode $categoryMode
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlRewriteFactory $rewriteFactory,
        BackendHelper $adminhtmlData,
        ProductMode $productMode,
        CategoryMode $categoryMode,
        array $data = []
    ) {
        $this->categoryMode = $categoryMode;
        $this->productMode  = $productMode;
        parent::__construct($context, $rewriteFactory, $adminhtmlData, $data);
    }

    /**
     * Prepare layout for URL rewrite creating for product
     *
     * @return void
     */
    protected function _prepareLayoutFeatures()
    {
        if ($this->_getUrlRewrite()->getId()) {
            $this->_headerText = __('Edit URL Rewrite for a Product');
        } else {
            $this->_headerText = __('Add URL Rewrite for a Product');
        }

        if ($this->_getProduct()->getId()) {
            $this->_addProductLinkBlock($this->_getProduct());
        }

        if ($this->_getCategory()->getId()) {
            $this->_addCategoryLinkBlock();
        }

        if ($this->_getProduct()->getId()) {
            if ($this->_getCategory()->getId() || !$this->getIsCategoryMode()) {
                $this->_addEditFormBlock();
                if ($this->_getUrlRewrite()->getId() === null) {
                    $productId = $this->_getProduct()->getId();
                    $this->_updateBackButtonLink(
                        $this->_adminhtmlData->getUrl('adminhtml/*/edit', ['product' => $productId]) . 'category'
                    );
                }
            } else {
                // categories selector & skip categories button
                $this->_addCategoriesTreeBlock();
                $this->_addSkipCategoriesBlock();
                if ($this->_getUrlRewrite()->getId() === null) {
                    $this->_updateBackButtonLink($this->_adminhtmlData->getUrl('adminhtml/*/edit') . 'product');
                }
            }
        } else {
            $this->_addUrlRewriteSelectorBlock();
            $this->_addProductsGridBlock();
        }
    }

    /**
     * Get or create new instance of product
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function _getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setProduct($this->productMode->getProduct($this->getUrlRewrite()));
        }
        return $this->getProduct();
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    private function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory($this->categoryMode->getCategory($this->getUrlRewrite()));
        }
        return $this->getCategory();
    }

    public function getIsCategoryMode()
    {
        return $this->getRequest()->has('category');
    }

    /**
     * Add child product link block
     *
     * @return void
     */
    private function _addProductLinkBlock()
    {
        $this->addChild(
            'product_link',
            'Magento\UrlRewrite\Block\Link',
            [
                'item_url' => $this->_adminhtmlData->getUrl(
                    'catalog/product/edit',
                    ['id' => $this->_getProduct()->getId()]
                ),
                'item_name' => $this->_getProduct()->getName(),
                'label' => __('Product:')
            ]
        );
    }

    /**
     * Add child category link block
     *
     * @return void
     */
    private function _addCategoryLinkBlock()
    {
        $this->addChild(
            'category_link',
            'Magento\UrlRewrite\Block\Link',
            [
                'item_url' => $this->_adminhtmlData->getUrl(
                    'catalog/category/edit',
                    ['id' => $this->_getCategory()->getId()]
                ),
                'item_name' => $this->_getCategory()->getName(),
                'label' => __('Category:')
            ]
        );
    }

    /**
     * Add child products grid block
     *
     * @return void
     */
    private function _addProductsGridBlock()
    {
        $this->addChild('products_grid', 'Magento\CatalogUrlRewrite\Block\Product\Grid');
    }

    /**
     * Add child Categories Tree block
     *
     * @return void
     */
    private function _addCategoriesTreeBlock()
    {
        $this->addChild('categories_tree', 'Magento\CatalogUrlRewrite\Block\Category\Tree');
    }

    /**
     * Add child Skip Categories block
     *
     * @return void
     */
    private function _addSkipCategoriesBlock()
    {
        $this->addChild(
            'skip_categories',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Skip Category Selection'),
                'onclick' => 'window.location = \'' . $this->_adminhtmlData->getUrl(
                    'adminhtml/*/*',
                    ['product' => $this->_getProduct()->getId()]
                ) . '\'',
                'class' => 'save',
                'level' => -1
            ]
        );
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\CatalogUrlRewrite\Block\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            'Magento\CatalogUrlRewrite\Block\Edit\Form',
            '',
            [
                'data' => [
                    'product' => $this->_getProduct(),
                    'category' => $this->_getCategory(),
                    'url_rewrite' => $this->_getUrlRewrite(),
                ]
            ]
        );
    }
}

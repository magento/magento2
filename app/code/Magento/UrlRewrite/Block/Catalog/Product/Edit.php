<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Product;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\UrlRewrite\Block\Catalog\Category\Tree;
use Magento\UrlRewrite\Block\Catalog\Edit\Form;
use Magento\UrlRewrite\Block\Edit as UrlRewriteEdit;
use Magento\UrlRewrite\Block\Link;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Block for Catalog Category URL rewrites editing
 */
class Edit extends UrlRewriteEdit
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param Context $context
     * @param UrlRewriteFactory $rewriteFactory
     * @param BackendHelper $adminhtmlData
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlRewriteFactory $rewriteFactory,
        BackendHelper $adminhtmlData,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
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
            $this->_addProductLinkBlock();
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
     * @return Product
     */
    private function _getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setProduct($this->_productFactory->create());
        }
        return $this->getProduct();
    }

    /**
     * Get or create new instance of category
     *
     * @return Category
     */
    private function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory($this->_categoryFactory->create());
        }
        return $this->getCategory();
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
            Link::class,
            [
                'item_url' => $this->_adminhtmlData->getUrl('adminhtml/*/*') . 'product',
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
            Link::class,
            [
                'item_url' => $this->_adminhtmlData->getUrl(
                    'adminhtml/*/*',
                    ['product' => $this->_getProduct()->getId()]
                ) . 'category',
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
        $this->addChild('products_grid', Grid::class);
    }

    /**
     * Add child Categories Tree block
     *
     * @return void
     */
    private function _addCategoriesTreeBlock()
    {
        $this->addChild('categories_tree', Tree::class);
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
            Button::class,
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
     * @return Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            Form::class,
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

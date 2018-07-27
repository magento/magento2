<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Product;

/**
 * Block for Catalog Category URL rewrites editing
 */
class Edit extends \Magento\UrlRewrite\Block\Edit
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
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
     * @return \Magento\Catalog\Model\Product
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
     * @return \Magento\Catalog\Model\Category
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
            'Magento\UrlRewrite\Block\Link',
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
            'Magento\UrlRewrite\Block\Link',
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
        $this->addChild('products_grid', 'Magento\UrlRewrite\Block\Catalog\Product\Grid');
    }

    /**
     * Add child Categories Tree block
     *
     * @return void
     */
    private function _addCategoriesTreeBlock()
    {
        $this->addChild('categories_tree', 'Magento\UrlRewrite\Block\Catalog\Category\Tree');
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
     * @return \Magento\UrlRewrite\Block\Catalog\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            'Magento\UrlRewrite\Block\Catalog\Edit\Form',
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

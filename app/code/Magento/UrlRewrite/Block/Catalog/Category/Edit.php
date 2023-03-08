<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Category;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\UrlRewrite\Block\Catalog\Category\Tree as CategoryTree;
use Magento\UrlRewrite\Block\Catalog\Edit\Form;
use Magento\UrlRewrite\Block\Edit as UrlRewriteEdit;
use Magento\UrlRewrite\Block\Link;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Block for Catalog Category URL rewrites
 */
class Edit extends UrlRewriteEdit
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param Context $context
     * @param UrlRewriteFactory $rewriteFactory
     * @param BackendHelper $adminhtmlData
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlRewriteFactory $rewriteFactory,
        BackendHelper $adminhtmlData,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $rewriteFactory, $adminhtmlData, $data);
    }

    /**
     * Prepare layout for URL rewrite creating for category
     *
     * @return void
     */
    protected function _prepareLayoutFeatures()
    {
        if ($this->_getUrlRewrite()->getId()) {
            $this->_headerText = __('Edit URL Rewrite for a Category');
        } else {
            $this->_headerText = __('Add URL Rewrite for a Category');
        }

        if ($this->_getCategory()->getId()) {
            $this->_addCategoryLinkBlock();
            $this->_addEditFormBlock();
            if ($this->_getUrlRewrite()->getId() === null) {
                $this->_updateBackButtonLink($this->_adminhtmlData->getUrl('adminhtml/*/edit') . 'category');
            }
        } else {
            $this->_addUrlRewriteSelectorBlock();
            $this->_addCategoryTreeBlock();
        }
    }

    /**
     * Get or create new instance of category
     *
     * @return Product
     */
    private function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory($this->_categoryFactory->create());
        }
        return $this->getCategory();
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
                'item_url' => $this->_adminhtmlData->getUrl('adminhtml/*/*') . 'category',
                'item_name' => $this->_getCategory()->getName(),
                'label' => __('Category:')
            ]
        );
    }

    /**
     * Add child category tree block
     *
     * @return void
     */
    private function _addCategoryTreeBlock()
    {
        $this->addChild('categories_tree', CategoryTree::class);
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
            ['data' => ['category' => $this->_getCategory(), 'url_rewrite' => $this->_getUrlRewrite()]]
        );
    }
}

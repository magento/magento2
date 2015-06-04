<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Block\Category;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\CatalogUrlRewrite\Model\Mode\Category as CategoryMode;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
/**
 * Block for Catalog Category URL rewrites
 */
/**
 * @method \Magento\UrlRewrite\Model\UrlRewrite getUrlRewrite()
 * @method \Magento\Catalog\Model\Category getCategory()
 * @method Edit setCategory(\Magento\Catalog\Model\Category $category)
 */
class Edit extends \Magento\UrlRewrite\Block\Edit
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\Mode\Category
     */
    protected $categoryMode;

    /**
     * @param Context $context
     * @param UrlRewriteFactory $rewriteFactory
     * @param BackendHelper $adminhtmlData
     * @param CategoryMode $categoryMode
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlRewriteFactory $rewriteFactory,
        BackendHelper $adminhtmlData,
        CategoryMode $categoryMode,
        array $data = []
    ) {
        $this->categoryMode = $categoryMode;
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
     * @return \Magento\Catalog\Model\Category
     */
    private function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory($this->categoryMode->getCategory($this->getUrlRewrite()));
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
     * Add child category tree block
     *
     * @return void
     */
    private function _addCategoryTreeBlock()
    {
        $this->addChild('categories_tree', 'Magento\CatalogUrlRewrite\Block\Category\Tree');
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
                    'category' => $this->_getCategory(),
                    'url_rewrite' => $this->_getUrlRewrite()
                ]
            ]
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Category;

/**
 * Block for Catalog Category URL rewrites
 * @since 2.0.0
 */
class Edit extends \Magento\UrlRewrite\Block\Edit
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     * @since 2.0.0
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $rewriteFactory, $adminhtmlData, $data);
    }

    /**
     * Prepare layout for URL rewrite creating for category
     *
     * @return void
     * @since 2.0.0
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
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function _addCategoryLinkBlock()
    {
        $this->addChild(
            'category_link',
            \Magento\UrlRewrite\Block\Link::class,
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
     * @since 2.0.0
     */
    private function _addCategoryTreeBlock()
    {
        $this->addChild('categories_tree', \Magento\UrlRewrite\Block\Catalog\Category\Tree::class);
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\UrlRewrite\Block\Catalog\Edit\Form
     * @since 2.0.0
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            \Magento\UrlRewrite\Block\Catalog\Edit\Form::class,
            '',
            ['data' => ['category' => $this->_getCategory(), 'url_rewrite' => $this->_getUrlRewrite()]]
        );
    }
}

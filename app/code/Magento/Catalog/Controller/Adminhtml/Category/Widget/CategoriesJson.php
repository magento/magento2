<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category\Widget;

class CategoriesJson extends \Magento\Catalog\Controller\Adminhtml\Category\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context, $layoutFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Categories tree node (Ajax version)
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $selected = $this->getRequest()->getPost('selected', '');
            $category = $this->_objectManager->create(\Magento\Catalog\Model\Category::class)->load($categoryId);
            if ($category->getId()) {
                $this->_coreRegistry->register('category', $category);
                $this->_coreRegistry->register('current_category', $category);
            }
            $categoryTreeBlock = $this->_getCategoryTreeBlock()->setSelectedCategories(explode(',', $selected));
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setJsonData($categoryTreeBlock->getTreeJson($category));
        }
    }
}

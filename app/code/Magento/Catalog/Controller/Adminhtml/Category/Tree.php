<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Category\Tree
 *
 */
class Tree extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Tree Action
     * Retrieve category tree
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $categoryId = (int)$this->getRequest()->getParam('id');

        if ($storeId) {
            if (!$categoryId) {
                $store = $this->_objectManager
                    ->get(\Magento\Store\Model\StoreManagerInterface::class)
                    ->getStore($storeId);
                $rootId = $store->getRootCategoryId();
                $this->getRequest()->setParam('id', $rootId);
            }
        }

        $category = $this->_initCategory(true);
        if (!$category) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        $block = $this->layoutFactory->create()->createBlock(\Magento\Catalog\Block\Adminhtml\Category\Tree::class);
        $root = $block->getRoot();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'data' => $block->getTree(),
            'parameters' => [
                'text' => $block->buildNodeName($root),
                'draggable' => false,
                'allowDrop' => (bool)$root->getIsVisible(),
                'id' => (int)$root->getId(),
                'expanded' => (int)$block->getIsWasExpanded(),
                'store_id' => (int)$block->getStore()->getId(),
                'category_id' => (int)$category->getId(),
                'root_visible' => (int)$root->getIsVisible(),
            ],
        ]);
    }
}

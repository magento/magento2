<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Category\CategoriesJson
 *
 * @since 2.0.0
 */
class CategoriesJson extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @since 2.0.0
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
     * Get tree node (Ajax version)
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->setIsTreeWasExpanded(true);
        } else {
            $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->setIsTreeWasExpanded(false);
        }
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            $category = $this->_initCategory();
            if (!$category) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
            }
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setJsonData(
                $this->layoutFactory->create()->createBlock(\Magento\Catalog\Block\Adminhtml\Category\Tree::class)
                    ->getTreeJson($category)
            );
        }
    }
}

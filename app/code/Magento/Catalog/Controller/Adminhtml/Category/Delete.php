<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Category\Delete
 *
 * @since 2.0.0
 */
class Delete extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     * @since 2.0.0
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Delete category action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $categoryId = (int)$this->getRequest()->getParam('id');
        $parentId = null;
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $parentId = $category->getParentId();
                $this->_eventManager->dispatch('catalog_controller_category_delete', ['category' => $category]);
                $this->_auth->getAuthStorage()->setDeletedPath($category->getPath());
                $this->categoryRepository->delete($category);
                $this->messageManager->addSuccess(__('You deleted the category.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while trying to delete the category.'));
                return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => $parentId]);
    }
}

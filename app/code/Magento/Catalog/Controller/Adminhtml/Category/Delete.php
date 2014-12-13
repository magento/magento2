<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

class Delete extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * Delete category action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($categoryId) {
            try {
                $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                $this->_eventManager->dispatch('catalog_controller_category_delete', ['category' => $category]);

                $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setDeletedPath($category->getPath());

                $category->delete();
                $this->messageManager->addSuccess(__('You deleted the category.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while trying to delete the category.'));
                return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
    }
}

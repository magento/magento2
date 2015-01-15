<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Page;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Cms::page_delete');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('page_id');
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Magento\Cms\Model\Page');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The page has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', ['page_id' => $id]);
                return;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a page to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }
}

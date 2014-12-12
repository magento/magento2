<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Delete extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Delete newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $template = $this->_objectManager->create(
            'Magento\Newsletter\Model\Template'
        )->load(
            $this->getRequest()->getParam('id')
        );
        if ($template->getId()) {
            try {
                $template->delete();
                $this->messageManager->addSuccess(__('The newsletter template has been deleted.'));
                $this->_getSession()->setFormData(false);
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting this template.'));
            }
        }
        $this->_redirect('*/template');
    }
}

<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class Delete extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Delete transactional email action
     *
     * @return void
     */
    public function execute()
    {
        $template = $this->_initTemplate('id');
        if ($template->getId()) {
            try {
                // check if the template is currently used
                if (count($template->getSystemConfigPathsWhereUsedCurrently()) == 0) {
                    $template->delete();
                    // display success message
                    $this->messageManager->addSuccess(__('The email template has been deleted.'));
                    $this->_objectManager->get('Magento\Framework\App\ReinitableConfig')->reinit();
                    // go to grid
                    $this->_redirect('adminhtml/*/');
                    return;
                }
                // display error  message
                $this->messageManager->addError(__('The email template is currently being used.'));
                // redirect to edit form
                $this->_redirect('adminhtml/*/edit', ['id' => $template->getId()]);
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while deleting email template data. Please review log and try again.')
                );
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                // save data in session
                $this->_objectManager->get(
                    'Magento\Backend\Model\Session'
                )->setFormData(
                    $this->getRequest()->getParams()
                );
                // redirect to edit form
                $this->_redirect('adminhtml/*/edit', ['id' => $template->getId()]);
                return;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find an email template to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}

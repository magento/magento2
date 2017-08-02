<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

/**
 * Class \Magento\Email\Controller\Adminhtml\Email\Template\Delete
 *
 * @since 2.0.0
 */
class Delete extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Delete transactional email action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $template = $this->_initTemplate('id');
        if ($template->getId()) {
            try {
                // check if the template is currently used
                if (count($template->getSystemConfigPathsWhereCurrentlyUsed()) == 0) {
                    $template->delete();
                    // display success message
                    $this->messageManager->addSuccess(__('You deleted the email template.'));
                    $this->_objectManager->get(\Magento\Framework\App\ReinitableConfig::class)->reinit();
                    // go to grid
                    $this->_redirect('adminhtml/*/');
                    return;
                }
                // display error  message
                $this->messageManager->addError(__('The email template is currently being used.'));
                // redirect to edit form
                $this->_redirect('adminhtml/*/edit', ['id' => $template->getId()]);
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete email template data right now. Please review log and try again.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                // save data in session
                $this->_objectManager->get(
                    \Magento\Backend\Model\Session::class
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

<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class Remove extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')->load($id);

            if (!$model->getId()) {
                $this->_redirect('adminhtml/*/');
                return;
            }

            try {
                $model->setIsRemove(1)->save();
                $this->messageManager->addSuccess(__('The message has been removed.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __("We couldn't remove the messages because of an error."));
            }

            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_redirect('adminhtml/*/');
    }
}

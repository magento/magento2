<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\DataObject;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Validator\Exception;
use Magento\User\Controller\Adminhtml\User;
use Magento\User\Model\User as ModelUser;

class Validate extends User
{
    /**
     * AJAX customer validation action
     *
     * @return void
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(0);
        $errors = null;
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        try {
            /** @var $model ModelUser */
            $model = $this->_userFactory->create()->load($userId);
            $model->setData($this->_getAdminUserData($data));
            $errors = $model->validate();
        } catch (Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(MessageInterface::TYPE_ERROR) as $error) {
                $errors[] = $error->getText();
            }
        }

        if ($errors !== true && !empty($errors)) {
            foreach ($errors as $error) {
                $this->messageManager->addError($error);
            }
            $response->setError(1);
            $this->_view->getLayout()->initMessages();
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->representJson($response->toJson());
    }
}

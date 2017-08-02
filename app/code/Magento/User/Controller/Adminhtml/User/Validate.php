<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

/**
 * Class \Magento\User\Controller\Adminhtml\User\Validate
 *
 * @since 2.0.0
 */
class Validate extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * AJAX customer validation action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $errors = null;
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        try {
            /** @var $model \Magento\User\Model\User */
            $model = $this->_userFactory->create()->load($userId);
            $model->setData($this->_getAdminUserData($data));
            $errors = $model->validate();
        } catch (\Magento\Framework\Validator\Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR) as $error) {
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

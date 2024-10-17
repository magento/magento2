<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Validate variable value before save controller
 * @api
 * @since 100.0.2
 */
class Validate extends Variable implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Validate Action
     *
     * @return Json
     */
    public function execute()
    {
        $response = new DataObject(['error' => false]);
        $variable = $this->_initVariable();
        $variable->addData($this->getRequest()->getPost('variable'));
        $result = $variable->validate();
        if ($result instanceof Phrase) {
            $this->messageManager->addError($result->getText());
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response->toArray());
    }
}

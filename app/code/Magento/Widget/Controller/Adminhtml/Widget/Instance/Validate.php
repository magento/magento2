<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Validate extends Instance implements HttpPostActionInterface
{
    /**
     * Validate action
     *
     * @return void
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);
        $widgetInstance = $this->_initWidgetInstance();
        $result = $widgetInstance->validate();
        if ($result !== true && (is_string($result) || $result instanceof Phrase)) {
            $this->messageManager->addErrorMessage((string) $result);
            $this->_view->getLayout()->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $response = $response->toJson();
        $this->_translateInline->processResponseBody($response);
        $this->_response->representJson($response);
    }
}

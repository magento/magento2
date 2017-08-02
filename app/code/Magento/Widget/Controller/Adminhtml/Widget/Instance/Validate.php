<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Validate
 *
 * @since 2.0.0
 */
class Validate extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Validate action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $widgetInstance = $this->_initWidgetInstance();
        $result = $widgetInstance->validate();
        if ($result !== true && is_string($result)) {
            $this->messageManager->addError($result);
            $this->_view->getLayout()->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $response = $response->toJson();
        $this->_translateInline->processResponseBody($response);
        $this->_response->representJson($response);
    }
}

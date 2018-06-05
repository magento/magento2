<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Framework\App\ObjectManager;

/**
 * Loading widget options
 */
class LoadOptions extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    private $conditionsHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Widget\Helper\Conditions $conditionsHelper = null,
        \Psr\Log\LoggerInterface $logger = null,
        \Magento\Framework\Json\Helper\Data $jsonHelper = null
    ) {
        $this->conditionsHelper = $conditionsHelper ?: ObjectManager::getInstance()->get(
            \Magento\Widget\Helper\Conditions::class
        );
        $this->logger = $logger ?: ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $this->jsonHelper = $jsonHelper ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Json\Helper\Data::class
        );
        parent::__construct($context);
    }

    /**
     * Ajax responder for loading plugin options form
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            if ($paramsJson = $this->getRequest()->getParam('widget')) {
                $request = $this->jsonHelper->jsonDecode($paramsJson);
                if (is_array($request)) {
                    $optionsBlock = $this->_view->getLayout()->getBlock('wysiwyg_widget.options');
                    if (isset($request['widget_type'])) {
                        $optionsBlock->setWidgetType($request['widget_type']);
                    }
                    if (isset($request['values'])) {
                        $request['values'] = array_map('htmlspecialchars_decode', $request['values']);
                        if (isset($request['values']['conditions_encoded'])) {
                            $request['values']['conditions'] =
                                $this->conditionsHelper->decode($request['values']['conditions_encoded']);
                        }
                        $optionsBlock->setWidgetValues($request['values']);
                    }
                }
                $this->_view->renderLayout();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->sendErrorResponse($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->logger->critical($e);
            $this->sendErrorResponse(__('We\'re sorry, an error has occurred while loading widget options.'));
        }
    }

    /**
     * Sends response in case of exception.
     *
     * @param \Magento\Framework\Phrase|string $message
     * @return void
     */
    private function sendErrorResponse($message)
    {
        $result = ['error' => true, 'message' => (string)$message];
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}

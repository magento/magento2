<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Helper\Conditions;

/**
 * Action used to load plugin options form via ajax
 */
class LoadOptions extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var Conditions
     */
    private $conditionsHelper;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Context $context
     * @param Conditions $conditionsHelper
     * @param Json $json
     */
    public function __construct(
        Context $context,
        Conditions $conditionsHelper,
        Json $json
    ) {
        parent::__construct($context);
        $this->conditionsHelper = $conditionsHelper;
        $this->json = $json;
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
                $request = $this->json->unserialize($paramsJson);
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
        } catch (LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->json->serialize($result)
            );
        }
    }
}

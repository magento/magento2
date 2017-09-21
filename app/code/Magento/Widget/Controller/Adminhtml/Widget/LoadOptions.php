<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Framework\App\ObjectManager;

class LoadOptions extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    private $conditionsHelper;

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
                $request = $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)
                    ->jsonDecode($paramsJson);
                if (is_array($request)) {
                    $optionsBlock = $this->_view->getLayout()->getBlock('wysiwyg_widget.options');
                    if (isset($request['widget_type'])) {
                        $optionsBlock->setWidgetType($request['widget_type']);
                    }
                    if (isset($request['values'])) {
                        $request['values'] = array_map('htmlspecialchars_decode', $request['values']);
                        if (isset($request['values']['conditions_encoded'])) {
                            $request['values']['conditions'] =
                                $this->getConditionsHelper()->decode($request['values']['conditions_encoded']);
                        }
                        $optionsBlock->setWidgetValues($request['values']);
                    }
                }
                $this->_view->renderLayout();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
            );
        }
    }

    /**
     * @return \Magento\Widget\Helper\Conditions
     * @deprecated 100.1.4
     */
    private function getConditionsHelper()
    {
        if (!$this->conditionsHelper) {
            $this->conditionsHelper = ObjectManager::getInstance()->get(\Magento\Widget\Helper\Conditions::class);
        }

        return $this->conditionsHelper;
    }
}

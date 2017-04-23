<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

class LoadOptions extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonDataHelper;

    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $widgetInstanceFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonDataHelper,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetInstanceFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);

        $this->jsonDataHelper = $jsonDataHelper;
        $this->widgetInstanceFactory = $widgetInstanceFactory;
        $this->coreRegistry = $registry;
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
                $request = $this->jsonDataHelper->jsonDecode($paramsJson);
                if (is_array($request)) {
                    $optionsBlock = $this->_view->getLayout()->getBlock('wysiwyg_widget.options');
                    $widgetInstance = null;

                    if (isset($request['widget_type'])) {
                        $widgetInstance = $this->widgetInstanceFactory->create();
                        $widgetInstance->setType($request['widget_type']);
                        $optionsBlock->setWidgetType($request['widget_type']);
                        $this->coreRegistry->register('current_widget_instance', $widgetInstance, true);
                    }

                    if (isset($request['values'])) {
                        $request['values'] = array_map('htmlspecialchars_decode', $request['values']);

                        if ($widgetInstance) {
                            $widgetInstance->setWidgetParameters($request['values']);
                        }

                        $optionsBlock->setWidgetValues($request['values']);
                    }
                }
                $this->_view->renderLayout();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->jsonDataHelper->jsonEncode($result)
            );
        }
    }
}

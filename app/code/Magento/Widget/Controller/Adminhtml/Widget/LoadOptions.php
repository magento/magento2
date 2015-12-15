<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

class LoadOptions extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $widgetFactory;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        \Magento\Widget\Helper\Conditions $conditionsHelper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->widgetFactory = $widgetFactory;
        $this->conditionsHelper = $conditionsHelper;
        parent::__construct($context);
    }

    /**
     * Init dynamic widget instance object and set it to registry
     * @see /Magento/CatalogWidget/Block/Product/Widget/Conditions::_construct
     *
     * @param  string $conditionsEncoded
     * @return \Magento\Widget\Model\Widget\Instance|boolean
     */
    protected function initWidgetInstance($conditionsEncoded)
    {
        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->widgetFactory->create();
        $widgetInstance->setWidgetParameters([
            'conditions' => $this->conditionsHelper->decode($conditionsEncoded)
        ]);
        $this->coreRegistry->register('current_widget_instance', $widgetInstance);
        return $widgetInstance;
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
                $request = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonDecode($paramsJson);
                if (is_array($request)) {
                    // @see /Magento/Widget/Model/Widget::getWidgetDeclaration
                    if (isset($request['values']['conditions_encoded'])) {
                        $this->initWidgetInstance($request['values']['conditions_encoded']);
                    }
                    $optionsBlock = $this->_view->getLayout()->getBlock('wysiwyg_widget.options');
                    if (isset($request['widget_type'])) {
                        $optionsBlock->setWidgetType($request['widget_type']);
                    }
                    if (isset($request['values'])) {
                        $optionsBlock->setWidgetValues($request['values']);
                    }
                }
                $this->_view->renderLayout();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        }
    }
}

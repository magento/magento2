<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widgets management controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Controller\Adminhtml;

use Magento\Backend\App\Action;

class Widget extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Widget\Model\Widget\Config
     */
    protected $_widgetConfig;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     * @param \Magento\Widget\Model\Widget $widget
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Widget\Model\Widget\Config $widgetConfig,
        \Magento\Widget\Model\Widget $widget,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_widgetConfig = $widgetConfig;
        $this->_widget = $widget;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Wisywyg widget plugin main page
     *
     * @return void
     */
    public function indexAction()
    {
        // save extra params for widgets insertion form
        $skipped = $this->getRequest()->getParam('skip_widgets');
        $skipped = $this->_widgetConfig->decodeWidgetsFromQuery($skipped);

        $this->_coreRegistry->register('skip_widgets', $skipped);

        $this->_view->loadLayout('empty')->renderLayout();
    }

    /**
     * Ajax responder for loading plugin options form
     *
     * @return void
     */
    public function loadOptionsAction()
    {
        try {
            $this->_view->loadLayout('empty');
            if ($paramsJson = $this->getRequest()->getParam('widget')) {
                $request = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonDecode($paramsJson);
                if (is_array($request)) {
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
        } catch (\Magento\Framework\Model\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Format widget pseudo-code for inserting into wysiwyg editor
     *
     * @return void
     */
    public function buildWidgetAction()
    {
        $type = $this->getRequest()->getPost('widget_type');
        $params = $this->getRequest()->getPost('parameters', array());
        $asIs = $this->getRequest()->getPost('as_is');
        $html = $this->_widget->getWidgetDeclaration($type, $params, $asIs);
        $this->getResponse()->setBody($html);
    }
}

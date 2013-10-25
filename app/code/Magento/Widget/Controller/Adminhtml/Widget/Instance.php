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
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Manage Widgets Instance Controller
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

class Instance extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $_widgetFactory;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param \Magento\Core\Model\Logger $logger
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        \Magento\Core\Model\Logger $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_widgetFactory = $widgetFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return \Magento\Widget\Controller\Adminhtml\Widget\Instance
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_Widget::cms_widget_instance')
            ->_addBreadcrumb(__('CMS'),
                __('CMS'))
            ->_addBreadcrumb(__('Manage Widget Instances'),
                __('Manage Widget Instances'));
        return $this;
    }

    /**
     * Init widget instance object and set it to registry
     *
     * @return \Magento\Widget\Model\Widget\Instance|boolean
     */
    protected function _initWidgetInstance()
    {
        $this->_title(__('Frontend Apps'));

        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_widgetFactory->create();

        $code = $this->getRequest()->getParam('code', null);
        $instanceId = $this->getRequest()->getParam('instance_id', null);
        if ($instanceId) {
            $widgetInstance
                ->load($instanceId)
                ->setCode($code);
            if (!$widgetInstance->getId()) {
                $this->_getSession()->addError(
                    __('Please specify a correct widget.')
                );
                return false;
            }
        } else {
            // Widget id was not provided on the query-string.  Locate the widget instance
            // type (namespace\classname) based upon the widget code (aka, widget id).
            $themeId = $this->getRequest()->getParam('theme_id', null);
            $type = $code != null ? $widgetInstance->getWidgetReference('code', $code, 'type') : null;
            $widgetInstance
                ->setType($type)
                ->setCode($code)
                ->setThemeId($themeId);
        }
        $this->_coreRegistry->register('current_widget_instance', $widgetInstance);
        return $widgetInstance;
    }

    /**
     * Widget Instances Grid
     *
     */
    public function indexAction()
    {
        $this->_title(__('Frontend Apps'));

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * New widget instance action (forward to edit action)
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit widget instance action
     *
     */
    public function editAction()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if (!$widgetInstance) {
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($widgetInstance->getId() ? $widgetInstance->getTitle() : __('New Frontend App Instance'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Set body to response
     *
     * @param string $body
     * @return null
     */
    private function setBody($body)
    {
        $this->_translator->processResponseBody($body);

        $this->getResponse()->setBody($body);
    }

    /**
     * Validate action
     *
     */
    public function validateAction()
    {
        $response = new \Magento\Object();
        $response->setError(false);
        $widgetInstance = $this->_initWidgetInstance();
        $result = $widgetInstance->validate();
        if ($result !== true && is_string($result)) {
            $this->_getSession()->addError($result);
            $this->_initLayoutMessages('Magento\Adminhtml\Model\Session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $this->setBody($response->toJson());
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if (!$widgetInstance) {
            $this->_redirect('*/*/');
            return;
        }
        $widgetInstance->setTitle($this->getRequest()->getPost('title'))
            ->setStoreIds($this->getRequest()->getPost('store_ids', array(0)))
            ->setSortOrder($this->getRequest()->getPost('sort_order', 0))
            ->setPageGroups($this->getRequest()->getPost('widget_instance'))
            ->setWidgetParameters($this->getRequest()->getPost('parameters'));
        try {
            $widgetInstance->save();
            $this->_getSession()->addSuccess(
                __('The widget instance has been saved.')
            );
            if ($this->getRequest()->getParam('back', false)) {
                    $this->_redirect('*/*/edit', array(
                        'instance_id' => $widgetInstance->getId(),
                        '_current' => true
                    ));
            } else {
                $this->_redirect('*/*/');
            }
            return;
        } catch (\Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
            $this->_logger->logException($exception);
            $this->_redirect('*/*/edit', array('_current' => true));
            return;
        }
        $this->_redirect('*/*/');
        return;
    }

    /**
     * Delete Action
     *
     */
    public function deleteAction()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if ($widgetInstance) {
            try {
                $widgetInstance->delete();
                $this->_getSession()->addSuccess(
                    __('The widget instance has been deleted.')
                );
            } catch (\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
        return;
    }

    /**
     * Categories chooser Action (Ajax request)
     *
     */
    public function categoriesAction()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $isAnchorOnly = $this->getRequest()->getParam('is_anchor_only', 0);
        $chooser = $this->getLayout()
            ->createBlock('Magento\Adminhtml\Block\Catalog\Category\Widget\Chooser')
            ->setUseMassaction(true)
            ->setId($this->_objectManager->get('Magento\Core\Helper\Data')->uniqHash('categories'))
            ->setIsAnchorOnly($isAnchorOnly)
            ->setSelectedCategories(explode(',', $selected));
        $this->setBody($chooser->toHtml());
    }

    /**
     * Products chooser Action (Ajax request)
     *
     */
    public function productsAction()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $productTypeId = $this->getRequest()->getParam('product_type_id', '');
        $chooser = $this->getLayout()
            ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Widget\Chooser')
            ->setName($this->_objectManager->get('Magento\Core\Helper\Data')->uniqHash('products_grid_'))
            ->setUseMassaction(true)
            ->setProductTypeId($productTypeId)
            ->setSelectedProducts(explode(',', $selected));
        /* @var $serializer \Magento\Adminhtml\Block\Widget\Grid\Serializer */
        $serializer = $this->getLayout()->createBlock(
            'Magento\Adminhtml\Block\Widget\Grid\Serializer',
            '',
            array(
                'data' => array(
                    'grid_block' => $chooser,
                    'callback' => 'getSelectedProducts',
                    'input_element_name' => 'selected_products',
                    'reload_param_name' => 'selected_products'
                )
            )
        );
        $this->setBody($chooser->toHtml() . $serializer->toHtml());
    }

    /**
     * Blocks Action (Ajax request)
     *
     */
    public function blocksAction()
    {
        /* @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $layout = $this->getRequest()->getParam('layout');
        $selected = $this->getRequest()->getParam('selected', null);
        $blocksChooser = $this->getLayout()
            ->createBlock('Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container')
            ->setValue($selected)
            ->setArea($widgetInstance->getArea())
            ->setTheme($widgetInstance->getThemeId())
            ->setLayoutHandle($layout)
            ->setAllowedContainers($widgetInstance->getWidgetSupportedContainers());
        $this->setBody($blocksChooser->toHtml());
    }

    /**
     * Templates Chooser Action (Ajax request)
     *
     */
    public function templateAction()
    {
        /* @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $block = $this->getRequest()->getParam('block');
        $selected = $this->getRequest()->getParam('selected', null);
        $templateChooser = $this->getLayout()
            ->createBlock('Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Template')
            ->setSelected($selected)
            ->setWidgetTemplates($widgetInstance->getWidgetSupportedTemplatesByContainer($block));
        $this->setBody($templateChooser->toHtml());
    }

    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Widget::widget_instance');
    }
}

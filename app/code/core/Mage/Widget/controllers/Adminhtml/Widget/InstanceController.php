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
 * @category    Mage
 * @package     Mage_Widget
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admihtml Manage Widgets Instance Controller
 *
 * @category   Mage
 * @package    Mage_Widget
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Adminhtml_Widget_InstanceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Session getter
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session');
    }

    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return Mage_Widget_Adminhtml_Widget_InstanceController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Widget::cms_widget_instance')
            ->_addBreadcrumb(Mage::helper('Mage_Widget_Helper_Data')->__('CMS'),
                Mage::helper('Mage_Widget_Helper_Data')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('Mage_Widget_Helper_Data')->__('Manage Widget Instances'),
                Mage::helper('Mage_Widget_Helper_Data')->__('Manage Widget Instances'));
        return $this;
    }

    /**
     * Init widget instance object and set it to registry
     *
     * @return Mage_Widget_Model_Widget_Instance|boolean
     */
    protected function _initWidgetInstance()
    {
        $this->_title($this->__('CMS'))->_title($this->__('Widgets'));

        /** @var $widgetInstance Mage_Widget_Model_Widget_Instance */
        $widgetInstance = Mage::getModel('Mage_Widget_Model_Widget_Instance');

        $instanceId = $this->getRequest()->getParam('instance_id', null);
        $type = $this->getRequest()->getParam('type', null);
        $packageTheme = $this->getRequest()->getParam('package_theme', null);
        if ($packageTheme) {
            $packageTheme = str_replace('-', '/', $packageTheme);
        }

        if ($instanceId) {
            $widgetInstance->load($instanceId);
            if (!$widgetInstance->getId()) {
                $this->_getSession()->addError(Mage::helper('Mage_Widget_Helper_Data')->__('Wrong widget instance specified.'));
                return false;
            }
        } else {
            $widgetInstance->setType($type)
                ->setPackageTheme($packageTheme);
        }
        Mage::register('current_widget_instance', $widgetInstance);
        return $widgetInstance;
    }

    /**
     * Widget Instances Grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('CMS'))->_title($this->__('Widgets'));

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

        $this->_title($widgetInstance->getId() ? $widgetInstance->getTitle() : $this->__('New Instance'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Set body to response
     *
     * @param string $body
     */
    private function setBody($body)
    {
        Mage::getSingleton('Mage_Core_Model_Translate_Inline')->processResponseBody($body);
        $this->getResponse()->setBody($body);
    }

    /**
     * Validate action
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        $widgetInstance = $this->_initWidgetInstance();
        $result = $widgetInstance->validate();
        if ($result !== true && is_string($result)) {
            $this->_getSession()->addError($result);
            $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $this->setBody($response->toJson());
    }

    /**
     * Save action
     *
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
                Mage::helper('Mage_Widget_Helper_Data')->__('The widget instance has been saved.')
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
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
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
                    Mage::helper('Mage_Widget_Helper_Data')->__('The widget instance has been deleted.')
                );
            } catch (Exception $e) {
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
            ->createBlock('Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser')
            ->setUseMassaction(true)
            ->setId(Mage::helper('Mage_Core_Helper_Data')->uniqHash('categories'))
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
            ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser')
            ->setName(Mage::helper('Mage_Core_Helper_Data')->uniqHash('products_grid_'))
            ->setUseMassaction(true)
            ->setProductTypeId($productTypeId)
            ->setSelectedProducts(explode(',', $selected));
        /* @var $serializer Mage_Adminhtml_Block_Widget_Grid_Serializer */
        $serializer = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Grid_Serializer');
        $serializer->initSerializerBlock($chooser, 'getSelectedProducts', 'selected_products', 'selected_products');
        $this->setBody($chooser->toHtml().$serializer->toHtml());
    }

    /**
     * Blocks Action (Ajax request)
     *
     */
    public function blocksAction()
    {
        /* @var $widgetInstance age_Widget_Model_Widget_Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $layout = $this->getRequest()->getParam('layout');
        $selected = $this->getRequest()->getParam('selected', null);
        $blocksChooser = $this->getLayout()
            ->createBlock('Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Container')
            ->setValue($selected)
            ->setArea($widgetInstance->getArea())
            ->setPackage($widgetInstance->getPackage())
            ->setTheme($widgetInstance->getTheme())
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
        /* @var $widgetInstance Mage_Widget_Model_Widget_Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $block = $this->getRequest()->getParam('block');
        $selected = $this->getRequest()->getParam('selected', null);
        $templateChooser = $this->getLayout()
            ->createBlock('Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Template')
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
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Widget::widget_instance');
    }
}

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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Manage Newsletter Template Controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Newsletter_TemplateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed ()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')
            ->isAllowed('Mage_Newsletter::template');
    }

    /**
     * Set title of page
     *
     * @return Mage_Adminhtml_Newsletter_TemplateController
     */
    protected function _setTitle()
    {
        return $this->_title($this->__('Newsletter'))->_title($this->__('Newsletter Templates'));
    }

    /**
     * View Templates list
     *
     */
    public function indexAction ()
    {
        $this->_setTitle();

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('Mage_Newsletter::newsletter_template');
        $this->_addBreadcrumb(Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Templates'), Mage::helper('Mage_Newsletter_Helper_Data')->__('Newsletter Templates'));
        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Newsletter_Template', 'template'));
        $this->renderLayout();
    }

    /**
     * JSON Grid Action
     *
     */
    public function gridAction ()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Newsletter_Template_Grid')
            ->toHtml();
        $this->getResponse()->setBody($grid);
    }

    /**
     * Create new Newsletter Template
     *
     */
    public function newAction ()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Newsletter Template
     *
     */
    public function editAction ()
    {
        $this->_setTitle();

        $model = Mage::getModel('Mage_Newsletter_Model_Template');
        if ($id = $this->getRequest()->getParam('id')) {
            $model->load($id);
        }

        Mage::register('_current_template', $model);

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Newsletter::newsletter_template');

        if ($model->getId()) {
            $breadcrumbTitle = Mage::helper('Mage_Newsletter_Helper_Data')->__('Edit Template');
            $breadcrumbLabel = $breadcrumbTitle;
        }
        else {
            $breadcrumbTitle = Mage::helper('Mage_Newsletter_Helper_Data')->__('New Template');
            $breadcrumbLabel = Mage::helper('Mage_Newsletter_Helper_Data')->__('Create Newsletter Template');
        }

        $this->_title($model->getId() ? $model->getTemplateCode() : $this->__('New Template'));

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        if ($values = $this->_getSession()->getData('newsletter_template_form_data', true)) {
            $model->addData($values);
        }

        if ($editBlock = $this->getLayout()->getBlock('template_edit')) {
            $editBlock->setEditMode($model->getId() > 0);
        }

        $this->renderLayout();
    }

    /**
     * Drop Newsletter Template
     *
     */
    public function dropAction ()
    {
        $this->loadLayout('newsletter_template_preview');
        $this->renderLayout();
    }

    /**
     * Save Newsletter Template
     *
     */
    public function saveAction ()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/newsletter_template'));
        }
        $template = Mage::getModel('Mage_Newsletter_Model_Template');

        if ($id = (int)$request->getParam('id')) {
            $template->load($id);
        }

        try {
            $template->addData($request->getParams())
                ->setTemplateSubject($request->getParam('subject'))
                ->setTemplateCode($request->getParam('code'))
                ->setTemplateSenderEmail($request->getParam('sender_email'))
                ->setTemplateSenderName($request->getParam('sender_name'))
                ->setTemplateText($request->getParam('text'))
                ->setTemplateStyles($request->getParam('styles'))
                ->setModifiedAt(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());

            if (!$template->getId()) {
                $template->setTemplateType(Mage_Newsletter_Model_Template::TYPE_HTML);
            }
            if ($this->getRequest()->getParam('_change_type_flag')) {
                $template->setTemplateType(Mage_Newsletter_Model_Template::TYPE_TEXT);
                $template->setTemplateStyles('');
            }
            if ($this->getRequest()->getParam('_save_as_flag')) {
                $template->setId(null);
            }

            $template->save();

            $this->_getSession()->addSuccess($this->_getHelper()->__('The newsletter template has been saved.'));
            $this->_getSession()->setFormData(false);

            $this->_redirect('*/*');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(nl2br($e->getMessage()));
            $this->_getSession()->setData('newsletter_template_form_data',
                $this->getRequest()->getParams());
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                $this->_getHelper()->__('An error occurred while saving this template.')
            );
            $this->_getSession()->setData('newsletter_template_form_data', $this->getRequest()->getParams());
        }

        $this->_forward('new');
    }

    /**
     * Delete newsletter Template
     *
     */
    public function deleteAction ()
    {
        $template = Mage::getModel('Mage_Newsletter_Model_Template')
            ->load($this->getRequest()->getParam('id'));
        if ($template->getId()) {
            try {
                $template->delete();
                $this->_getSession()->addSuccess($this->_getHelper()->__('The newsletter template has been deleted.'));
                $this->_getSession()->setFormData(false);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    $this->_getHelper()->__('An error occurred while deleting this template.')
                );
            }
        }
        $this->_redirect('*/*');
    }

    /**
     * Preview Newsletter template
     *
     */
    public function previewAction ()
    {
        $this->_setTitle();
        $this->loadLayout();

        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noRoute');
            return $this;
        }

        // set default value for selected store
        $data['preview_store_id'] = Mage::app()->getDefaultStoreView()->getId();

        $this->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->renderLayout();
    }
}

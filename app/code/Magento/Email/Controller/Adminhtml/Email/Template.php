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
namespace Magento\Email\Controller\Adminhtml\Email;

/**
 * System Template admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Template extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Email Templates'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Email::template');
        $this->_addBreadcrumb(__('Transactional Emails'), __('Transactional Emails'));
        $this->_view->renderLayout();
    }

    /**
     * Grid action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * New transactional email action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit transactional email action
     *
     * @return void
     */
    public function editAction()
    {
        $this->_view->loadLayout();
        $template = $this->_initTemplate('id');
        $this->_setActiveMenu('Magento_Email::template');
        $this->_addBreadcrumb(__('Transactional Emails'), __('Transactional Emails'), $this->getUrl('adminhtml/*'));

        if ($this->getRequest()->getParam('id')) {
            $this->_addBreadcrumb(__('Edit Template'), __('Edit System Template'));
        } else {
            $this->_addBreadcrumb(__('New Template'), __('New System Template'));
        }

        $this->_title->add($template->getId() ? $template->getTemplateCode() : __('New Template'));

        $this->_addContent(
            $this->_view->getLayout()->createBlock(
                'Magento\Email\Block\Adminhtml\Template\Edit',
                'template_edit'
            )->setEditMode(
                (bool)$this->getRequest()->getParam('id')
            )
        );
        $this->_view->renderLayout();
    }

    /**
     * Save transactional email action
     *
     * @return void
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $id = $this->getRequest()->getParam('id');

        $template = $this->_initTemplate('id');
        if (!$template->getId() && $id) {
            $this->messageManager->addError(__('This email template no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        try {
            $template->setTemplateSubject(
                $request->getParam('template_subject')
            )->setTemplateCode(
                $request->getParam('template_code')
            )->setTemplateText(
                $request->getParam('template_text')
            )->setTemplateStyles(
                $request->getParam('template_styles')
            )->setModifiedAt(
                $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate()
            )->setOrigTemplateCode(
                $request->getParam('orig_template_code')
            )->setOrigTemplateVariables(
                $request->getParam('orig_template_variables')
            );

            if (!$template->getId()) {
                $template->setTemplateType(\Magento\Email\Model\Template::TYPE_HTML);
            }

            if ($request->getParam('_change_type_flag')) {
                $template->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT);
                $template->setTemplateStyles('');
            }

            $template->save();
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
            $this->messageManager->addSuccess(__('The email template has been saved.'));
            $this->_redirect('adminhtml/*');
        } catch (\Exception $e) {
            $this->_objectManager->get(
                'Magento\Backend\Model\Session'
            )->setData(
                'email_template_form_data',
                $request->getParams()
            );
            $this->messageManager->addError($e->getMessage());
            $this->_forward('new');
        }
    }

    /**
     * Delete transactional email action
     *
     * @return void
     */
    public function deleteAction()
    {
        $template = $this->_initTemplate('id');
        if ($template->getId()) {
            try {
                // check if the template is currently used
                if (count($template->getSystemConfigPathsWhereUsedCurrently()) == 0) {
                    $template->delete();
                    // display success message
                    $this->messageManager->addSuccess(__('The email template has been deleted.'));
                    $this->_objectManager->get('Magento\Framework\App\ReinitableConfig')->reinit();
                    // go to grid
                    $this->_redirect('adminhtml/*/');
                    return;
                }
                // display error  message
                $this->messageManager->addError(__('The email template is currently being used.'));
                // redirect to edit form
                $this->_redirect('adminhtml/*/edit', array('id' => $template->getId()));
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while deleting email template data. Please review log and try again.')
                );
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                // save data in session
                $this->_objectManager->get(
                    'Magento\Backend\Model\Session'
                )->setFormData(
                    $this->getRequest()->getParams()
                );
                // redirect to edit form
                $this->_redirect('adminhtml/*/edit', array('id' => $template->getId()));
                return;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find an email template to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Preview transactional email action
     *
     * @return void
     */
    public function previewAction()
    {
        try {
            $this->_view->loadLayout('systemPreview');
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred. The email template can not be opened for preview.'));
            $this->_redirect('adminhtml/*/');
        }
    }

    /**
     * Set template data to retrieve it in template info form
     *
     * @return void
     */
    public function defaultTemplateAction()
    {
        $this->_view->loadLayout();
        $template = $this->_initTemplate('id');
        $templateCode = $this->getRequest()->getParam('code');
        try {
            $template->loadDefault($templateCode);
            $template->setData('orig_template_code', $templateCode);
            $template->setData('template_variables', \Zend_Json::encode($template->getVariablesOptionArray(true)));

            $templateBlock = $this->_view->getLayout()->createBlock('Magento\Email\Block\Adminhtml\Template\Edit');
            $template->setData('orig_template_used_default_for', $templateBlock->getUsedDefaultForPaths(false));

            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($template->getData())
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }

    /**
     * Load email template from request
     *
     * @param string $idFieldName
     * @return \Magento\Email\Model\BackendTemplate $model
     */
    protected function _initTemplate($idFieldName = 'template_id')
    {
        $this->_title->add(__('Email Templates'));

        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = $this->_objectManager->create('Magento\Email\Model\BackendTemplate');
        if ($id) {
            $model->load($id);
        }
        if (!$this->_coreRegistry->registry('email_template')) {
            $this->_coreRegistry->register('email_template', $model);
        }
        if (!$this->_coreRegistry->registry('current_email_template')) {
            $this->_coreRegistry->register('current_email_template', $model);
        }
        return $model;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Email::template');
    }
}

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
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Manage Newsletter Template Controller
 *
 * @category   Magento
 * @package    Magento_Newsletter
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Controller\Adminhtml;

class Template extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Magento_Newsletter::template');
    }

    /**
     * Set title of page
     *
     * @return $this
     */
    protected function _setTitle()
    {
        return $this->_title->add(__('Newsletter Templates'));
    }

    /**
     * View Templates list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_setTitle();

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Newsletter::newsletter_template');
        $this->_addBreadcrumb(__('Newsletter Templates'), __('Newsletter Templates'));
        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Newsletter\Block\Adminhtml\Template', 'template'));
        $this->_view->renderLayout();
    }

    /**
     * JSON Grid Action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock('Magento\Newsletter\Block\Adminhtml\Template\Grid')
            ->toHtml();
        $this->getResponse()->setBody($grid);
    }

    /**
     * Create new Newsletter Template
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Newsletter Template
     *
     * @return void
     */
    public function editAction()
    {
        $this->_setTitle();

        $model = $this->_objectManager->create('Magento\Newsletter\Model\Template');
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model->load($id);
        }

        $this->_coreRegistry->register('_current_template', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Newsletter::newsletter_template');

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit Template');
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New Template');
            $breadcrumbLabel = __('Create Newsletter Template');
        }

        $this->_title->add($model->getId() ? $model->getTemplateCode() : __('New Template'));

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData('newsletter_template_form_data', true);
        if ($values) {
            $model->addData($values);
        }

        $editBlock = $this->_view->getLayout()->getBlock('template_edit');
        if ($editBlock) {
            $editBlock->setEditMode($model->getId() > 0);
        }

        $this->_view->renderLayout();
    }

    /**
     * Drop Newsletter Template
     *
     * @return void
     */
    public function dropAction()
    {
        $this->_view->loadLayout('newsletter_template_preview_popup');
        $this->_view->renderLayout();
    }

    /**
     * Save Newsletter Template
     *
     * @return void
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/template'));
        }
        $template = $this->_objectManager->create('Magento\Newsletter\Model\Template');

        $id = (int)$request->getParam('id');
        if ($id) {
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
                ->setModifiedAt($this->_objectManager->get('Magento\Core\Model\Date')->gmtDate());

            if (!$template->getId()) {
                $template->setTemplateType(\Magento\Newsletter\Model\Template::TYPE_HTML);
            }
            if ($this->getRequest()->getParam('_change_type_flag')) {
                $template->setTemplateType(\Magento\Newsletter\Model\Template::TYPE_TEXT);
                $template->setTemplateStyles('');
            }
            if ($this->getRequest()->getParam('_save_as_flag')) {
                $template->setId(null);
            }

            $template->save();

            $this->messageManager->addSuccess(__('The newsletter template has been saved.'));
            $this->_getSession()->setFormData(false);

            $this->_redirect('*/template');
            return;
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_getSession()->setData('newsletter_template_form_data',
                $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->messageManager->addException($e,
                __('An error occurred while saving this template.')
            );
            $this->_getSession()->setData('newsletter_template_form_data', $this->getRequest()->getParams());
        }

        $this->_forward('new');
    }

    /**
     * Delete newsletter Template
     *
     * @return void
     */
    public function deleteAction()
    {
        $template = $this->_objectManager->create('Magento\Newsletter\Model\Template')
            ->load($this->getRequest()->getParam('id'));
        if ($template->getId()) {
            try {
                $template->delete();
                $this->messageManager->addSuccess(__('The newsletter template has been deleted.'));
                $this->_getSession()->setFormData(false);
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e,
                    __('An error occurred while deleting this template.')
                );
            }
        }
        $this->_redirect('*/template');
    }

    /**
     * Preview Newsletter template
     *
     * @return void|$this
     */
    public function previewAction()
    {
        $this->_setTitle();
        $this->_view->loadLayout();

        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noroute');
            return $this;
        }

        // set default value for selected store
        $data['preview_store_id'] = $this->_objectManager->get('Magento\Core\Model\StoreManager')
            ->getDefaultStoreView()->getId();

        $this->_view->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->_view->renderLayout();
    }
}

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
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;

/**
 * Custom Variables admin controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Variable extends Action
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
     * Initialize Layout and set breadcrumbs
     *
     * @return $this
     */
    protected function _initLayout()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Backend::system_variable'
        )->_addBreadcrumb(
            __('Custom Variables'),
            __('Custom Variables')
        );
        return $this;
    }

    /**
     * Initialize Variable object
     *
     * @return \Magento\Core\Model\Variable
     */
    protected function _initVariable()
    {
        $this->_title->add(__('Custom Variables'));

        $variableId = $this->getRequest()->getParam('variable_id', null);
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        /* @var $variable \Magento\Core\Model\Variable */
        $variable = $this->_objectManager->create('Magento\Core\Model\Variable');
        if ($variableId) {
            $variable->setStoreId($storeId)->load($variableId);
        }
        $this->_coreRegistry->register('current_variable', $variable);
        return $variable;
    }

    /**
     * Index Action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Custom Variables'));

        $this->_initLayout();
        $this->_view->renderLayout();
    }

    /**
     * New Action (forward to edit action)
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Action
     *
     * @return void
     */
    public function editAction()
    {
        $variable = $this->_initVariable();

        $this->_title->add($variable->getId() ? $variable->getCode() : __('New Custom Variable'));

        $this->_initLayout()->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Backend\Block\System\Variable\Edit')
        )->_addJs(
            $this->_view->getLayout()->createBlock(
                'Magento\Framework\View\Element\Template',
                '',
                array('data' => array('template' => 'Magento_Backend::system/variable/js.phtml'))
            )
        );
        $this->_view->renderLayout();
    }

    /**
     * Validate Action
     *
     * @return void
     */
    public function validateAction()
    {
        $response = new \Magento\Framework\Object(array('error' => false));
        $variable = $this->_initVariable();
        $variable->addData($this->getRequest()->getPost('variable'));
        $result = $variable->validate();
        if ($result !== true && is_string($result)) {
            $this->messageManager->addError($result);
            $this->_view->getLayout()->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * Save Action
     *
     * @return void
     */
    public function saveAction()
    {
        $variable = $this->_initVariable();
        $data = $this->getRequest()->getPost('variable');
        $back = $this->getRequest()->getParam('back', false);
        if ($data) {
            $data['variable_id'] = $variable->getId();
            $variable->setData($data);
            try {
                $variable->save();
                $this->messageManager->addSuccess(__('You saved the custom variable.'));
                if ($back) {
                    $this->_redirect(
                        'adminhtml/*/edit',
                        array('_current' => true, 'variable_id' => $variable->getId())
                    );
                } else {
                    $this->_redirect('adminhtml/*/', array());
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', array('_current' => true));
                return;
            }
        }
        $this->_redirect('adminhtml/*/', array());
        return;
    }

    /**
     * Delete Action
     *
     * @return void
     */
    public function deleteAction()
    {
        $variable = $this->_initVariable();
        if ($variable->getId()) {
            try {
                $variable->delete();
                $this->messageManager->addSuccess(__('You deleted the customer.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', array('_current' => true));
                return;
            }
        }
        $this->_redirect('adminhtml/*/', array());
        return;
    }

    /**
     * WYSIWYG Plugin Action
     *
     * @return void
     */
    public function wysiwygPluginAction()
    {
        $customVariables = $this->_objectManager->create('Magento\Core\Model\Variable')->getVariablesOptionArray(true);
        $storeContactVariabls = $this->_objectManager->create(
            'Magento\Email\Model\Source\Variables'
        )->toOptionArray(
            true
        );
        $variables = array($storeContactVariabls, $customVariables);
        $this->getResponse()->representJson(\Zend_Json::encode($variables));
    }

    /**
     * Check current user permission
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::variable');
    }
}

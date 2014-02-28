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
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax rule controller
 *
 * @category   Magento
 * @package    Magento_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Controller\Adminhtml;

use Magento\Backend\App\Action;

class Rule extends \Magento\Backend\App\Action
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
     * @return $this
     */
    public function indexAction()
    {
        $this->_title->add(__('Tax Rules'));
        $this->_initAction();
        $this->_view->renderLayout();

        return $this;
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Tax Rules'));

        $taxRuleId  = $this->getRequest()->getParam('rule');
        $ruleModel  = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rule');
        if ($taxRuleId) {
            $ruleModel->load($taxRuleId);
            if (!$ruleModel->getId()) {
                $this->_objectManager->get('Magento\Backend\Model\Session')->unsRuleData();
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('tax/*/');
                return;
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getRuleData(true);
        if (!empty($data)) {
            $ruleModel->setData($data);
        }

        $this->_title->add($ruleModel->getId() ? sprintf("%s", $ruleModel->getCode()) : __('New Tax Rule'));

        $this->_coreRegistry->register('tax_rule', $ruleModel);

        $this->_initAction()
            ->_addBreadcrumb($taxRuleId ? __('Edit Rule') :  __('New Rule'), $taxRuleId ?  __('Edit Rule') :  __('New Rule'));
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {

            $ruleModel = $this->_objectManager->get('Magento\Tax\Model\Calculation\Rule');
            $ruleModel->setData($postData);

            try {
                $ruleModel->save();

                $this->messageManager->addSuccess(__('The tax rule has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('tax/*/edit', array('rule' => $ruleModel->getId()));
                    return;
                }

                $this->_redirect('tax/*/');
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong saving this tax rule.'));
            }

            $this->_objectManager->get('Magento\Backend\Model\Session')->setRuleData($postData);
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        }
        $this->getResponse()->setRedirect($this->getUrl('tax/rule'));
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $ruleId = (int)$this->getRequest()->getParam('rule');
        $ruleModel = $this->_objectManager->get('Magento\Tax\Model\Calculation\Rule')
            ->load($ruleId);
        if (!$ruleModel->getId()) {
            $this->messageManager->addError(__('This rule no longer exists'));
            $this->_redirect('tax/*/');
            return;
        }

        try {
            $ruleModel->delete();

            $this->messageManager->addSuccess(__('The tax rule has been deleted.'));
            $this->_redirect('tax/*/');

            return;
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong deleting this tax rule.'));
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Tax::sales_tax_rules')
            ->_addBreadcrumb(__('Tax'), __('Tax'))
            ->_addBreadcrumb(__('Tax Rules'), __('Tax Rules'));
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
    }
}

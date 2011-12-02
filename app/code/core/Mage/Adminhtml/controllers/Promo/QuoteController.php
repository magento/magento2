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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Adminhtml_Promo_QuoteController extends Mage_Adminhtml_Controller_Action
{
    protected function _initRule()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Shopping Cart Price Rules'));

        Mage::register('current_promo_quote_rule', Mage::getModel('Mage_SalesRule_Model_Rule'));
        if ($id = (int) $this->getRequest()->getParam('id')) {
            Mage::registry('current_promo_quote_rule')
                ->load($id);
        }
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/quote')
            ->_addBreadcrumb(Mage::helper('Mage_SalesRule_Helper_Data')->__('Promotions'), Mage::helper('Mage_SalesRule_Helper_Data')->__('Promotions'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Shopping Cart Price Rules'));

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_SalesRule_Helper_Data')->__('Catalog'), Mage::helper('Mage_SalesRule_Helper_Data')->__('Catalog'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('Mage_SalesRule_Model_Rule');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_SalesRule_Helper_Data')->__('This rule no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getRuleId() ? $model->getName() : $this->__('New Rule'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');

        Mage::register('current_promo_quote_rule', $model);

        $this->_initAction()->getLayout()->getBlock('promo_quote_edit')
             ->setData('action', $this->getUrl('*/*/save'));

        $this
            ->_addBreadcrumb(
                $id ? Mage::helper('Mage_SalesRule_Helper_Data')->__('Edit Rule')
                    : Mage::helper('Mage_SalesRule_Helper_Data')->__('New Rule'),
                $id ? Mage::helper('Mage_SalesRule_Helper_Data')->__('Edit Rule')
                    : Mage::helper('Mage_SalesRule_Helper_Data')->__('New Rule'))
            ->renderLayout();

    }

    /**
     * Promo quote save action
     *
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('Mage_SalesRule_Model_Rule');
                Mage::dispatchEvent(
                    'adminhtml_controller_salesrule_prepare_save',
                    array('request' => $this->getRequest()));
                $data = $this->getRequest()->getPost();

                //filter HTML tags
                /** @var $helper Mage_Adminhtml_Helper_Data */
                $helper = Mage::helper('Mage_Adminhtml_Helper_Data');
                $data['name'] = $helper->stripTags($data['name']);
                $data['description'] = $helper->stripTags($data['description']);
                foreach ($data['store_labels'] as &$label) {
                    $label = $helper->stripTags($label);
                }

                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('Mage_SalesRule_Helper_Data')->__('Wrong rule specified.'));
                    }
                }

                $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                && isset($data['discount_amount'])) {
                    $data['discount_amount'] = min(100,$data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $model->loadPost($data);

                $session->setPageData($model->getData());

                $model->save();
                $session->addSuccess(Mage::helper('Mage_SalesRule_Helper_Data')->__('The rule has been saved.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('Mage_CatalogRule_Helper_Data')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setPageData($data);
                 $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('Mage_SalesRule_Model_Rule');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_SalesRule_Helper_Data')->__('The rule has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('Mage_CatalogRule_Helper_Data')->__('An error occurred while deleting the rule. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
            Mage::helper('Mage_SalesRule_Helper_Data')->__('Unable to find a rule to delete.'));
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('Mage_SalesRule_Model_Rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('Mage_SalesRule_Model_Rule'))
            ->setPrefix('actions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function applyRulesAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Chooser source action
     */
    public function chooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $chooserBlock = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Promo_Widget_Chooser', '', array(
            'id' => $uniqId
        ));
        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('promo/quote');
    }
}

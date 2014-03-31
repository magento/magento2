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
 * @package     Magento_CatalogRule
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend Catalog Price Rules controller
 *
 * @category    Magento
 * @package     Magento_CatalogRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Model\Rule\Job;
use Magento\Model\Exception;
use Magento\Stdlib\DateTime\Filter\Date;
use Magento\Registry;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Action\AbstractAction;

class Catalog extends Action
{
    /**
     * Dirty rules notice message
     *
     *
     * @var string
     */
    protected $_dirtyRulesNoticeMessage;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(Context $context, Registry $coreRegistry, Date $dateFilter)
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_dateFilter = $dateFilter;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_CatalogRule::promo_catalog'
        )->_addBreadcrumb(
            __('Promotions'),
            __('Promotions')
        );
        return $this;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Catalog Price Rules'));

        $dirtyRules = $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf();
        if ($dirtyRules->getState()) {
            $this->messageManager->addNotice($this->getDirtyRulesNoticeMessage());
        }

        $this->_initAction()->_addBreadcrumb(__('Catalog'), __('Catalog'));
        $this->_view->renderLayout();
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
        $this->_title->add(__('Catalog Price Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magento\CatalogRule\Model\Rule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('catalog_rule/*');
                return;
            }
        }

        $this->_title->add($model->getRuleId() ? $model->getName() : __('New Catalog Price Rule'));

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->_coreRegistry->register('current_promo_catalog_rule', $model);

        $this->_initAction();
        $this->_view->getLayout()->getBlock(
            'promo_catalog_edit'
        )->setData(
            'action',
            $this->getUrl('catalog_rule/promo_catalog/save')
        );

        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = $this->_objectManager->create('Magento\CatalogRule\Model\Rule');
                $this->_eventManager->dispatch(
                    'adminhtml_controller_catalogrule_prepare_save',
                    array('request' => $this->getRequest())
                );
                $data = $this->getRequest()->getPost();
                $inputFilter = new \Zend_Filter_Input(
                    array('from_date' => $this->_dateFilter, 'to_date' => $this->_dateFilter),
                    array(),
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new Exception(__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validateData(new \Magento\Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('catalog_rule/*/edit', array('id' => $model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);

                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());

                $model->save();

                $this->messageManager->addSuccess(__('The rule has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf()->setState(1)->save();
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('catalog_rule/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('catalog_rule/*/');
                }
                return;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while saving the rule data. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('catalog_rule/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('catalog_rule/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Magento\CatalogRule\Model\Rule');
                $model->load($id);
                $model->delete();
                $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf()->setState(1)->save();
                $this->messageManager->addSuccess(__('The rule has been deleted.'));
                $this->_redirect('catalog_rule/*/');
                return;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while deleting the rule. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->_redirect('catalog_rule/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addError(__('Unable to find a rule to delete.'));
        $this->_redirect('catalog_rule/*/');
    }

    /**
     * @return void
     */
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create('Magento\CatalogRule\Model\Rule')
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * @return void
     */
    public function chooserAction()
    {
        if ($this->getRequest()->getParam('attribute') == 'sku') {
            $type = 'Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku';
        }
        if (!empty($type)) {
            $block = $this->_view->getLayout()->createBlock($type);
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }

    /**
     * @return void
     */
    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create('Magento\CatalogRule\Model\Rule')
        )->setPrefix(
            'actions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractAction) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Apply all active catalog price rules
     *
     * @return void
     */
    public function applyRulesAction()
    {
        $errorMessage = __('Unable to apply rules.');
        try {
            /** @var Job $ruleJob */
            $ruleJob = $this->_objectManager->get('Magento\CatalogRule\Model\Rule\Job');
            $ruleJob->applyAll();

            if ($ruleJob->hasSuccess()) {
                $this->messageManager->addSuccess($ruleJob->getSuccess());
                $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf()->setState(0)->save();
            } elseif ($ruleJob->hasError()) {
                $this->messageManager->addError($errorMessage . ' ' . $ruleJob->getError());
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($errorMessage);
        }
        $this->_redirect('catalog_rule/*');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_CatalogRule::promo_catalog');
    }

    /**
     * Set dirty rules notice message
     *
     * @param string $dirtyRulesNoticeMessage
     * @return void
     */
    public function setDirtyRulesNoticeMessage($dirtyRulesNoticeMessage)
    {
        $this->_dirtyRulesNoticeMessage = $dirtyRulesNoticeMessage;
    }

    /**
     * Get dirty rules notice message
     *
     * @return string
     */
    public function getDirtyRulesNoticeMessage()
    {
        $defaultMessage = __(
            'There are rules that have been changed but were not applied. Please, click Apply Rules in order to see immediate effect in the catalog.'
        );
        return $this->_dirtyRulesNoticeMessage ? $this->_dirtyRulesNoticeMessage : $defaultMessage;
    }
}

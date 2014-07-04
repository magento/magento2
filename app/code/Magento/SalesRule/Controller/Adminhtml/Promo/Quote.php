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
namespace Magento\SalesRule\Controller\Adminhtml\Promo;

class Quote extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_dateFilter = $dateFilter;
    }

    /**
     * Initiate rule
     *
     * @return void
     */
    protected function _initRule()
    {
        $this->_title->add(__('Cart Price Rules'));

        $this->_coreRegistry->register(
            'current_promo_quote_rule',
            $this->_objectManager->create('Magento\SalesRule\Model\Rule')
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int)$this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            $this->_coreRegistry->registry('current_promo_quote_rule')->load($id);
        }
    }

    /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_SalesRule::promo_quote')->_addBreadcrumb(__('Promotions'), __('Promotions'));
        return $this;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Cart Price Rules'));

        $this->_initAction()->_addBreadcrumb(__('Catalog'), __('Catalog'));
        $this->_view->renderLayout();
    }

    /**
     * New promo quote action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Promo quote edit action
     *
     * @return void
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magento\SalesRule\Model\Rule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('sales_rule/*');
                return;
            }
        }

        $this->_title->add($model->getRuleId() ? $model->getName() : __('New Cart Price Rule'));

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');

        $this->_coreRegistry->register('current_promo_quote_rule', $model);

        $this->_initAction();
        $this->_view->getLayout()->getBlock('promo_quote_edit')->setData('action', $this->getUrl('sales_rule/*/save'));

        $this->_addBreadcrumb($id ? __('Edit Rule') : __('New Rule'), $id ? __('Edit Rule') : __('New Rule'));
        $this->_view->renderLayout();
    }

    /**
     * Promo quote save action
     *
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                /** @var $model \Magento\SalesRule\Model\Rule */
                $model = $this->_objectManager->create('Magento\SalesRule\Model\Rule');
                $this->_eventManager->dispatch(
                    'adminhtml_controller_salesrule_prepare_save',
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
                        throw new \Magento\Framework\Model\Exception(__('The wrong rule is specified.'));
                    }
                }

                $session = $this->_objectManager->get('Magento\Backend\Model\Session');

                $validateResult = $model->validateData(new \Magento\Framework\Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('sales_rule/*/edit', array('id' => $model->getId()));
                    return;
                }

                if (isset(
                    $data['simple_action']
                ) && $data['simple_action'] == 'by_percent' && isset(
                    $data['discount_amount']
                )
                ) {
                    $data['discount_amount'] = min(100, $data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $model->loadPost($data);

                $useAutoGeneration = (int)(!empty($data['use_auto_generation']));
                $model->setUseAutoGeneration($useAutoGeneration);

                $session->setPageData($model->getData());

                $model->save();
                $this->messageManager->addSuccess(__('The rule has been saved.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('sales_rule/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('sales_rule/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('sales_rule/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('sales_rule/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while saving the rule data. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('sales_rule/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('sales_rule/*/');
    }

    /**
     * Delete promo quote action
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Magento\SalesRule\Model\Rule');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The rule has been deleted.'));
                $this->_redirect('sales_rule/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while deleting the rule. Please review the log and try again.')
                );
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->_redirect('sales_rule/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        $this->_redirect('sales_rule/*/');
    }

    /**
     * New condition html action
     *
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
            $this->_objectManager->create('Magento\SalesRule\Model\Rule')
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * New action html action
     *
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
            $this->_objectManager->create('Magento\SalesRule\Model\Rule')
        )->setPrefix(
            'actions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Apply rules action
     *
     * @return void
     */
    public function applyRulesAction()
    {
        $this->_initAction();
        $this->_view->renderLayout();
    }

    /**
     * Coupon codes grid
     *
     * @return void
     */
    public function couponsGridAction()
    {
        $this->_initRule();
        $this->_view->loadLayout()->renderLayout();
    }

    /**
     * Export coupon codes as excel xml file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     */
    public function exportCouponsXmlAction()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry('current_promo_quote_rule');
        if ($rule->getId()) {
            $fileName = 'coupon_codes.xml';
            $content = $this->_view->getLayout()->createBlock(
                'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid'
            )->getExcelFile(
                $fileName
            );
            return $this->_fileFactory->create($fileName, $content, \Magento\Framework\App\Filesystem::VAR_DIR);
        } else {
            $this->_redirect('sales_rule/*/detail', array('_current' => true));
            return;
        }
    }

    /**
     * Export coupon codes as CSV file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     */
    public function exportCouponsCsvAction()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry('current_promo_quote_rule');
        if ($rule->getId()) {
            $fileName = 'coupon_codes.csv';
            $content = $this->_view->getLayout()->createBlock(
                'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid'
            )->getCsvFile();
            return $this->_fileFactory->create($fileName, $content, \Magento\Framework\App\Filesystem::VAR_DIR);
        } else {
            $this->_redirect('sales_rule/*/detail', array('_current' => true));
            return;
        }
    }

    /**
     * Coupons mass delete action
     *
     * @return void
     */
    public function couponsMassDeleteAction()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry('current_promo_quote_rule');

        if (!$rule->getId()) {
            $this->_forward('noroute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {

            $couponsCollection = $this->_objectManager->create(
                'Magento\SalesRule\Model\Resource\Coupon\Collection'
            )->addFieldToFilter(
                'coupon_id',
                array('in' => $codesIds)
            );

            foreach ($couponsCollection as $coupon) {
                $coupon->delete();
            }
        }
    }

    /**
     * Generate Coupons action
     *
     * @return void
     */
    public function generateAction()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        $result = array();
        $this->_initRule();

        /** @var $rule \Magento\SalesRule\Model\Rule */
        $rule = $this->_coreRegistry->registry('current_promo_quote_rule');

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } else {
            try {
                $data = $this->getRequest()->getParams();
                if (!empty($data['to_date'])) {
                    $inputFilter = new \Zend_Filter_Input(array('to_date' => $this->_dateFilter), array(), $data);
                    $data = $inputFilter->getUnescaped();
                }

                /** @var $generator \Magento\SalesRule\Model\Coupon\Massgenerator */
                $generator = $this->_objectManager->get('Magento\SalesRule\Model\Coupon\Massgenerator');
                if (!$generator->validateData($data)) {
                    $result['error'] = __('Invalid data provided');
                } else {
                    $generator->setData($data);
                    $generator->generatePool();
                    $generated = $generator->getGeneratedCount();
                    $this->messageManager->addSuccess(__('%1 coupon(s) have been generated.', $generated));
                    $this->_view->getLayout()->initMessages();
                    $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
                }
            } catch (\Magento\Framework\Model\Exception $e) {
                $result['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Chooser source action
     *
     * @return void
     */
    public function chooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $chooserBlock = $this->_view->getLayout()->createBlock(
            'Magento\SalesRule\Block\Adminhtml\Promo\Widget\Chooser',
            '',
            array('data' => array('id' => $uniqId))
        );
        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_SalesRule::quote');
    }
}

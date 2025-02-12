<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Filter\FilterInput;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * SalesRule save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote implements HttpPostActionInterface
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param TimezoneInterface $timezone
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        ?TimezoneInterface $timezone = null,
        ?DataPersistorInterface $dataPersistor = null
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->timezone =  $timezone ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            TimezoneInterface::class
        );
        $this->dataPersistor = $dataPersistor ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            DataPersistorInterface::class
        );
    }

    /**
     * Promo quote save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data['simple_free_shipping'] = (($data['simple_free_shipping'] ?? '') === '')
                    ? null : $data['simple_free_shipping'];

            try {
                /** @var $model \Magento\SalesRule\Model\Rule */
                $model = $this->_objectManager->create(\Magento\SalesRule\Model\Rule::class);
                $this->_eventManager->dispatch(
                    'adminhtml_controller_salesrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                if (empty($data['from_date'])) {
                    $data['from_date'] = $this->timezone->formatDate();
                }

                $filterValues = ['from_date' => $this->_dateFilter];
                if ($this->getRequest()->getParam('to_date')) {
                    $filterValues['to_date'] = $this->_dateFilter;
                }
                $inputFilter = new FilterInput(
                    $filterValues,
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                if (!$this->checkRuleExists($model)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The wrong rule is specified.'));
                }

                $session = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->dataPersistor->set('sale_rule', $data);
                    $this->_redirect('sales_rule/*/edit', ['id' => $model->getId()]);
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

                $data = $this->updateCouponData($data);
                $model->loadPost($data);

                $useAutoGeneration = (int)(
                    !empty($data['use_auto_generation']) && $data['use_auto_generation'] !== 'false'
                );
                $model->setUseAutoGeneration($useAutoGeneration);
                $session->setPageData($model->getData());

                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('sales_rule/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('sales_rule/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('sales_rule/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('sales_rule/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->_redirect('sales_rule/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('sales_rule/*/');
    }

    /**
     * Check if Cart Price Rule with provided id exists.
     *
     * @param \Magento\SalesRule\Model\Rule $model
     * @return bool
     */
    private function checkRuleExists(\Magento\SalesRule\Model\Rule $model): bool
    {
        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $model->load($id);
            if ($model->getId() != $id) {
                return false;
            }
        }
        return true;
    }

    /**
     * Update data related to Coupon
     *
     * @param array $data
     * @return array
     */
    private function updateCouponData(array $data): array
    {
        if (isset($data['uses_per_coupon']) && $data['uses_per_coupon'] === '') {
            $data['uses_per_coupon'] = 0;
        }
        if (isset($data['uses_per_customer']) && $data['uses_per_customer'] === '') {
            $data['uses_per_customer'] = 0;
        }
        return $data;
    }
}

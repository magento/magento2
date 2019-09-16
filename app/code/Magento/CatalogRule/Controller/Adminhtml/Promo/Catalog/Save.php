<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\LocalizedToNormalizedFactory;
use Magento\Framework\Filter\NormalizedToLocalizedFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var LocalizedToNormalizedFactory
     */
    private $localizedToNormalizedFactory;

    /**
     * @var NormalizedToLocalizedFactory
     */
    private $normalizedToLocalizedFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param DataPersistorInterface $dataPersistor
     * @param TimezoneInterface $localeDate
     * @param LocalizedToNormalizedFactory $localizedToNormalizedFactory
     * @param NormalizedToLocalizedFactory $normalizedToLocalizedFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor,
        TimezoneInterface $localeDate,
        LocalizedToNormalizedFactory $localizedToNormalizedFactory,
        NormalizedToLocalizedFactory $normalizedToLocalizedFactory
    ) {
        parent::__construct($context, $coreRegistry, $dateFilter);
        $this->dataPersistor = $dataPersistor;
        $this->localeDate = $localeDate;
        $this->localizedToNormalizedFactory = $localizedToNormalizedFactory;
        $this->normalizedToLocalizedFactory = $normalizedToLocalizedFactory;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {

            /** @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $ruleRepository */
            $ruleRepository = $this->_objectManager->get(
                \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class
            );

            /** @var \Magento\CatalogRule\Model\Rule $model */
            $model = $this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class);

            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_catalogrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPostValue();

                $data = $this->formatDateFields($data);
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model = $ruleRepository->get($id);
                }

                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->dataPersistor->set('catalog_rule', $data);
                    $this->_redirect('catalog_rule/*/edit', ['id' => $model->getId()]);
                    return;
                }

                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }

                unset($data['conditions_serialized']);
                unset($data['actions_serialized']);

                $model->loadPost($data);

                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->dataPersistor->set('catalog_rule', $data);

                $ruleRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData(false);
                $this->dataPersistor->clear('catalog_rule');

                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    if ($model->isRuleBehaviorChanged()) {
                        $this->_objectManager
                            ->create(\Magento\CatalogRule\Model\Flag::class)
                            ->loadSelf()
                            ->setState(1)
                            ->save();
                    }
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('catalog_rule/*/edit', ['id' => $model->getId()]);
                        return;
                    }
                    $this->_redirect('catalog_rule/*/');
                }
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->dataPersistor->set('catalog_rule', $data);
                $this->_redirect('catalog_rule/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('catalog_rule/*/');
    }

    /**
     * Format date fields from localized to internal format.
     *
     * @param array $data
     * @return array
     */
    private function formatDateFields(array $data): array
    {
        $filterInput = $this->localizedToNormalizedFactory->create(
            [
                'options' => [
                    'locale' => $this->_localeResolver->getLocale(),
                    'date_format' => $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                ],
            ]
        );
        $filterInternal = $this->normalizedToLocalizedFactory->create(
            [
                'options' => [
                    'date_format' => DateTime::DATE_INTERNAL_FORMAT,
                ],
            ]
        );

        foreach ($data as $fieldName => $fieldValue) {
            if (in_array($fieldName, ['from_date', 'to_date']) && !empty($fieldValue)) {
                $fieldValue = $filterInput->filter($fieldValue);
                $fieldValue = $filterInternal->filter($fieldValue);
                $data[$fieldName] = $fieldValue;
            }
        }

        return $data;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\AccountConfirmation;

/**
 * Class Confirmation column.
 */
class Confirmation extends Column
{
    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig @deprecated
     * @param array $components
     * @param array $data
     * @param AccountConfirmation|null $accountConfirmation
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ScopeConfigInterface $scopeConfig,
        array $components,
        array $data,
        ?AccountConfirmation $accountConfirmation = null
    ) {
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->getFieldLabel($item);
            }
        }
        return $dataSource;
    }

    /**
     * Retrieve field label
     *
     * @param array $item
     * @return string
     */
    private function getFieldLabel(array $item)
    {
        if ($this->getIsConfirmationRequired($item)) {
            if ($item[$this->getData('name')] === null) {
                return __('Confirmed');
            }
            return __('Confirmation Required');
        }
        return __('Confirmation Not Required');
    }

    /**
     * Retrieve is confirmation required flag for customer considering requested website may not exist.
     *
     * @param array $customer
     * @return bool
     */
    private function getIsConfirmationRequired(array $customer): bool
    {
        try {
            return $this->accountConfirmation->isConfirmationRequired(
                $customer['website_id'][0] ?? null,
                $customer[$customer['id_field_name']],
                $customer['email']
            );
        } catch (NoSuchEntityException $e) {
            return $this->accountConfirmation->isConfirmationRequired(
                null,
                $customer[$customer['id_field_name']],
                $customer['email']
            );
        }
    }
}

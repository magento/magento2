<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * @param AccountConfirmation $accountConfirmation
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ScopeConfigInterface $scopeConfig,
        array $components,
        array $data,
        AccountConfirmation $accountConfirmation = null
    ) {
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
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
        $isConfirmationRequired = $this->accountConfirmation->isConfirmationRequired(
            $item['website_id'][0],
            $item[$item['id_field_name']],
            $item['email']
        );

        if ($isConfirmationRequired) {
            if ($item[$this->getData('name')] === null) {
                return __('Confirmed');
            }
            return __('Confirmation Required');
        }
        return __('Confirmation Not Required');
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Renders the payment method title configured for the store of each grid row.
 *
 * Reads the numeric store_id of the row, so the column is declared before the Purchase Point column that rewrites it.
 */
class StorePaymentMethod extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item[$name])) {
                continue;
            }
            $storeId = isset($item['store_id']) && is_numeric($item['store_id']) ? (int)$item['store_id'] : null;
            $title = (string)$this->scopeConfig->getValue(
                sprintf('payment/%s/title', $item[$name]),
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($title !== '') {
                $item[$name] = $title;
            }
        }

        return $dataSource;
    }
}

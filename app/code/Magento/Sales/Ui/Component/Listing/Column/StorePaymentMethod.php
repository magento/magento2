<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Renders the payment method title configured for the store of each grid row.
 *
 * The Purchase Point column rewrites store_id in the row, so store ids are read from the grid table by entity_id.
 */
class StorePaymentMethod extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ResourceConnection $resourceConnection,
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
        $storeIds = $this->loadStoreIds($dataSource['data']['items'], $name);
        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item[$name])) {
                continue;
            }
            $title = $this->getTitle((string)$item[$name], $storeIds[$item['entity_id'] ?? null] ?? null);
            if ($title !== '') {
                $item[$name] = $title;
            }
        }

        return $dataSource;
    }

    /**
     * Load store ids of the page rows from the grid table.
     *
     * @param array $items
     * @param string $name
     * @return int[] store ids by entity id
     */
    private function loadStoreIds(array $items, string $name): array
    {
        $entityIds = [];
        foreach ($items as $item) {
            if (!empty($item[$name]) && isset($item['entity_id'])) {
                $entityIds[] = $item['entity_id'];
            }
        }
        if (!$entityIds) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection('sales');
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('sales_order_grid', 'sales'), ['entity_id', 'store_id'])
            ->where('entity_id IN (?)', $entityIds);

        return array_map('intval', $connection->fetchPairs($select));
    }

    /**
     * Get the payment method title for a store, using the default scope when the store no longer exists.
     *
     * @param string $code
     * @param int|null $storeId
     * @return string
     */
    private function getTitle(string $code, ?int $storeId): string
    {
        $path = sprintf('payment/%s/title', $code);
        try {
            return (string)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        } catch (NoSuchEntityException) {
            return (string)$this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        }
    }
}

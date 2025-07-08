<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Eav\Model\ResourceModel\Entity\Attribute;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Provide option value
 */
class OptionValueProvider
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @param ResourceConnection $connection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $connection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->connection = $connection->getConnection();
        $this->storeManager = $storeManager;
    }

    /**
     * Get EAV attribute option value by option id
     *
     * @param int $optionId
     * @return string|null
     */
    public function get(int $optionId): ?string
    {
        $storeId = $this->storeManager->getStore()->getId();
        $select = $this->connection->select()
            ->from($this->connection->getTableName('eav_attribute_option_value'), ['store_id', 'value'])
            ->where('option_id = ?', $optionId);

        $records = $this->connection->fetchAssoc($select);
        if (empty($records)) {
            return null;
        }

        return $records[$storeId]['value'] ?? $records[0]['value'];
    }
}

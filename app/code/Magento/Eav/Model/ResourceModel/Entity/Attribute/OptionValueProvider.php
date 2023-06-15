<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    /**
     * Get EAV attribute option value by option id
     *
     * @param int $optionId
     * @return string|null
     */
    public function get(int $optionId): ?string
    {
        $select = $this->connection->select()
            ->from($this->connection->getTableName('eav_attribute_option_value'), 'value')
            ->where('option_id = ?', $optionId);

        $result = $this->connection->fetchOne($select);

        if ($result !== false) {
            return $result;
        }

        return null;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * QuoteIdMask Resource model
 * @codeCoverageIgnore
 */
class QuoteIdMask extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_id_mask', 'entity_id');
    }

    /**
     * Retrieves masked quote id
     *
     * Uses direct DB query due to performance reasons
     *
     * @param int $quoteId
     * @return string|null
     */
    public function getMaskedQuoteId(int $quoteId): ?string
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $field = $connection->quoteIdentifier(sprintf('%s.%s', $mainTable, 'quote_id'));

        $select = $connection->select()
            ->from($mainTable, ['masked_id'])
            ->where($field . '=?', $quoteId);

        $result = $connection->fetchOne($select);

        return $result ?: null;
    }
}

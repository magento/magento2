<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class UpdateValidationResult
 * This class updates the quoteAddress validation without calling quote addess save
 * See: https://github.com/magento/magento2/issues/12612
 */
class UpdateValidationResult
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * UpdateValidationResult constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param \Magento\Framework\DataObject $validationResult
     */
    public function execute(
        \Magento\Quote\Model\Quote\Address $quoteAddress,
        \Magento\Framework\DataObject $validationResult
    ) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('quote_address');

        $connection->update($tableName, [
            'vat_is_valid' => (int) $validationResult->getIsValid(),
            'vat_request_id' => $validationResult->getRequestIdentifier(),
            'vat_request_date' => $validationResult->getRequestDate(),
            'vat_request_success' => $validationResult->getRequestSuccess(),
        ], $connection->quoteInto('address_id=?', $quoteAddress->getId()));
    }
}

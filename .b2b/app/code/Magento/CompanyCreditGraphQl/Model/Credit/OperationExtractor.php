<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyCreditGraphQl\Model\Credit;

use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\NegotiableQuote\Model\PriceCurrency;

/**
 * Extract operation details
 */
class OperationExtractor
{
    /**
     * @var HistoryType
     */
    private $historyType;

    /**
     * @var OperationUser
     */
    private $operationUser;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param HistoryType $historyType
     * @param OperationUser $operationUser
     * @param Balance $balance
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        HistoryType $historyType,
        OperationUser $operationUser,
        Balance $balance,
        PriceCurrency $priceCurrency
    ) {
        $this->historyType = $historyType;
        $this->operationUser = $operationUser;
        $this->balance = $balance;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Extract credit history data
     *
     * @param HistoryInterface $creditOperation
     * @return array
     */
    public function extractOperation(HistoryInterface $creditOperation): array
    {
        return [
            'amount' => $this->balance->formatData(
                $creditOperation->getCurrencyOperation(),
                (float)$creditOperation->getAmount(),
                $this->priceCurrency->format((float)$creditOperation->getAmount(),false,null,null,$creditOperation->getCurrencyOperation())
            ),
            'date' => $creditOperation->getDatetime(),
            'custom_reference_number' => $creditOperation->getCustomReferenceNumber(),
            'type' => $this->historyType->getHistoryType((int)$creditOperation->getType()),
            'updated_by' => [
                'name' => $this->operationUser->getUserName(
                    (int)$creditOperation->getUserType(),
                    (int)$creditOperation->getUserId()
                ),
                'type' => $this->operationUser->getUserType((int)$creditOperation->getUserType())
            ],
            'balance' => $this->balance->getBalance($creditOperation)
        ];
    }
}

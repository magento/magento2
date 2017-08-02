<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Transaction\Grid;

use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * Sales transaction types option array
 * @since 2.0.0
 */
class TypeList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var TransactionRepositoryInterface
     * @since 2.0.0
     */
    protected $transactionRepository;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @since 2.0.0
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Return option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->transactionRepository->create()->getTransactionTypes();
    }
}

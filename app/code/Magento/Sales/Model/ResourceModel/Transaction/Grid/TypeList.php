<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Transaction\Grid;

use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * Sales transaction types option array
 */
class TypeList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->transactionRepository->create()->getTransactionTypes();
    }
}

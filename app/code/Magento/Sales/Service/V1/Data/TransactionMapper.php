<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace Magento\Sales\Service\V1\Data;

use \Magento\Sales\Model\Order\Payment\Transaction as TrasactionModel;

class TransactionMapper
{
    /**
     * @var TransactionBuilderFactory
     */
    private $transactionBuilderFactory;

    /**
     * @var Transaction\AdditionalInformationBuilder
     */
    private $additionalInfoBuilder;

    /**
     * @var TransactionMapperFactory
     */
    private $transactionMapperFactory;

    /**
     * @param TransactionBuilderFactory $transactionBuilderFactory
     * @param Transaction\AdditionalInformationBuilder $additionalInfoBuilder
     * @param TransactionMapperFactory $transactionMapperFactory
     */
    public function __construct(
        TransactionBuilderFactory $transactionBuilderFactory,
        Transaction\AdditionalInformationBuilder $additionalInfoBuilder,
        TransactionMapperFactory $transactionMapperFactory
    ) {
        $this->transactionBuilderFactory = $transactionBuilderFactory;
        $this->additionalInfoBuilder = $additionalInfoBuilder;
        $this->transactionMapperFactory = $transactionMapperFactory;
    }

    /**
     * Converts additional_info from array of strings to array of Transaction\AdditionalInformation
     *
     * @param TrasactionModel $transactionModel
     * @return Transaction\AdditionalInformation[]
     */
    public function getAdditionalInfo(TrasactionModel $transactionModel)
    {
        $additionalInfo = [];
        foreach ($transactionModel->getAdditionalInformation() as $key => $value) {
            $this->additionalInfoBuilder->populateWithArray(
                [
                    Transaction\AdditionalInformation::KEY => $key,
                    Transaction\AdditionalInformation::VALUE => $value
                ]
            );
            $additionalInfo[] = $this->additionalInfoBuilder->create();
        }
        return $additionalInfo;
    }

    /**
     * Returns order increment id
     *
     * @param TrasactionModel $transactionModel
     * @return string
     */
    public function getIncrementId(TrasactionModel $transactionModel)
    {
        $order = $transactionModel->getOrder();
        return $order->getIncrementId();
    }

    /**
     * Returns array of Transaction[] (child transactions are not loaded recursively)
     *
     * @param TrasactionModel $transactionModel
     * @return Transaction[]
     */
    public function getChildTransactions(TrasactionModel $transactionModel)
    {
        $childTransactions = [];
        foreach ($transactionModel->getChildTransactions() as $childTransactionModel) {
            /** @var TransactionMapper $transactionMapper */
            $transactionMapper = $this->transactionMapperFactory->create();
            $childTransactionModel->setMethod($transactionModel->getMethod());
            $childTransactions[] = $transactionMapper->extractDto($childTransactionModel, true);
        }

        return $childTransactions;
    }

    /**
     * @param TrasactionModel $transactionModel
     * @param bool $lazy
     * @return Transaction
     */
    public function extractDto(TrasactionModel $transactionModel, $lazy = false)
    {
        /** @var TransactionBuilder $transactionBuilder */
        $transactionBuilder = $this->transactionBuilderFactory->create();
        $transactionBuilder->setTransactionId($transactionModel->getTransactionId());
        $transactionBuilder->setParentId($transactionModel->getParentId());
        $transactionBuilder->setOrderId($transactionModel->getOrderId());
        $transactionBuilder->setTxnId($transactionModel->getTxnId());
        $transactionBuilder->setPaymentId($transactionModel->getPaymentId());
        $transactionBuilder->setParentTxnId($transactionModel->getParentTxnId());
        $transactionBuilder->setTxnType($transactionModel->getTxnType());
        $transactionBuilder->setIsClosed($transactionModel->getIsClosed());
        $transactionBuilder->setAdditionalInformation($this->getAdditionalInfo($transactionModel));
        $transactionBuilder->setCreatedAt($transactionModel->getCreatedAt());
        $transactionBuilder->setMethod($transactionModel->getMethod());
        $transactionBuilder->setIncrementId($this->getIncrementId($transactionModel));
        $transactionBuilder->setChildTransactions($lazy ? [] : $this->getChildTransactions($transactionModel));
        return $transactionBuilder->create();
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Vault\Api\Data;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;

/**
 * Class PaymentTokenNullRepository
 */
class PaymentTokenNullRepository implements PaymentTokenRepositoryInterface
{
    /**
     * @var PaymentTokenFactory
     */
    private $tokenFactory;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Constructor
     *
     * @param PaymentTokenFactory $tokenFactory
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        PaymentTokenFactory $tokenFactory,
        PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $results = $this->searchResultsFactory->create();
        $results->setSearchCriteria($searchCriteria);
        $results->setItems([]);

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        return $this->tokenFactory->create();
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function delete(Data\PaymentTokenInterface $paymentToken)
    {
        throw new \LogicException(sprintf('You must implement this operation. (%s)', __METHOD__));
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     */
    public function save(Data\PaymentTokenInterface $paymentToken)
    {
        throw new \LogicException(sprintf('You must implement this operation. (%s)', __METHOD__));
    }
}

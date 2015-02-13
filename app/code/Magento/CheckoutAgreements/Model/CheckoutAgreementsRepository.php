<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CheckoutAgreements\Model;

use Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\CheckoutAgreements\Model\Resource\Agreement\Collection as AgreementCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;

/**
 * Checkout agreement repository.
 */
class CheckoutAgreementsRepository implements CheckoutAgreementsRepositoryInterface
{
    /**
     * Collection factory.
     *
     * @var AgreementCollectionFactory
     */
    private $collectionFactory;

    /**
     * Store manager.
     *
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Scope config.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructs a checkout agreement data object.
     *
     * @param AgreementCollectionFactory $collectionFactory Collection factory.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager.
     * @param ScopeConfigInterface $scopeConfig Scope config.
     */
    public function __construct(
        AgreementCollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface[] Array of checkout agreement data objects.
     */
    public function getList()
    {
        if (!$this->scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE)) {
            return [];
        }
        $storeId = $this->storeManager->getStore()->getId();
        /** @var $agreementCollection AgreementCollection */
        $agreementCollection = $this->collectionFactory->create();
        $agreementCollection->addStoreFilter($storeId);
        $agreementCollection->addFieldToFilter('is_active', 1);

        $agreementDataObjects = [];
        foreach ($agreementCollection as $agreement) {
            $agreementDataObjects[] = $agreement;
        }

        return $agreementDataObjects;
    }
}

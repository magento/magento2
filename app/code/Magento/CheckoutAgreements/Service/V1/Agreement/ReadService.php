<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Service\V1\Agreement;

use Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\CheckoutAgreements\Model\Resource\Agreement\Collection as AgreementCollection;
use Magento\CheckoutAgreements\Model\Agreement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Service\V1\Data\AgreementBuilder;
use Magento\CheckoutAgreements\Service\V1\Data\Agreement as AgreementDataObject;

/**
 * Checkout agreement service.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Collection factory.
     *
     * @var AgreementCollectionFactory
     */
    private $collectionFactory;

    /**
     * Agreement builder.
     *
     * @var AgreementBuilder
     */
    private $agreementBuilder;

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
     * Constructs a checkout agreement service object.
     *
     * @param AgreementCollectionFactory $collectionFactory Collection factory.
     * @param AgreementBuilder $agreementBuilder Agreement builder.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager.
     * @param ScopeConfigInterface $scopeConfig Scope config.
     */
    public function __construct(
        AgreementCollectionFactory $collectionFactory,
        AgreementBuilder $agreementBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->agreementBuilder = $agreementBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @return array|\Magento\CheckoutAgreements\Service\V1\Data\Agreement[] Array of checkout agreement service objects.
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
            $agreementDataObjects[] = $this->createAgreementDataObject($agreement);
        }

        return $agreementDataObjects;
    }

    /**
     * Creates an agreement data object based on a specified agreement model.
     *
     * @param Agreement $agreement The agreement model.
     * @return AgreementDataObject Agreement data object.
     */
    protected function createAgreementDataObject(Agreement $agreement)
    {
        $this->agreementBuilder->populateWithArray([
            AgreementDataObject::ID => $agreement->getId(),
            AgreementDataObject::NAME => $agreement->getName(),
            AgreementDataObject::CONTENT => $agreement->getContent(),
            AgreementDataObject::CONTENT_HEIGHT => $agreement->getContentHeight(),
            AgreementDataObject::CHECKBOX_TEXT => $agreement->getCheckboxText(),
            AgreementDataObject::ACTIVE => (bool)$agreement->getIsActive(),
            AgreementDataObject::HTML => (bool)$agreement->getIsHtml(),
        ]);
        return $this->agreementBuilder->create();
    }
}

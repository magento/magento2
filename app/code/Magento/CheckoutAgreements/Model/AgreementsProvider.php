<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;

/**
 * Provide Agreements stored in db
 * @since 2.0.0
 */
class AgreementsProvider implements AgreementsProviderInterface
{
    /**
     * Path to config node
     */
    const PATH_ENABLED = 'checkout/options/enable_agreements';

    /**
     * @var AgreementCollectionFactory
     * @since 2.0.0
     */
    protected $agreementCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @param AgreementCollectionFactory $agreementCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        AgreementCollectionFactory $agreementCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->agreementCollectionFactory = $agreementCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get list of required Agreement Ids
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getRequiredAgreementIds()
    {
        $agreementIds = [];
        if ($this->scopeConfig->isSetFlag(self::PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            $agreementCollection = $this->agreementCollectionFactory->create();
            $agreementCollection->addStoreFilter($this->storeManager->getStore()->getId());
            $agreementCollection->addFieldToFilter('is_active', 1);
            $agreementCollection->addFieldToFilter('mode', AgreementModeOptions::MODE_MANUAL);
            $agreementIds = $agreementCollection->getAllIds();
        }
        return $agreementIds;
    }
}

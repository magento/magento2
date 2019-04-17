<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreementsGraphQl\Model\Resolver\DataProvider;

use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\Agreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checkout Agreements data provider
 */
class CheckoutAgreements
{
    /**
     * @var CollectionFactory
     */
    private $agreementCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CollectionFactory $agreementCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $agreementCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->agreementCollectionFactory = $agreementCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get All Active Checkout Agreements Data
     *
     * @return array
     */
    public function getData(): array
    {
        $agreementsCollection = $this->agreementCollectionFactory->create();
        $agreementsCollection->addStoreFilter($this->storeManager->getStore()->getId()); // TODO: store should be get from query context
        $agreementsCollection->addFieldToFilter('is_active', 1);

        $checkoutAgreementData = [];
        /** @var Agreement $checkoutAgreement */
        foreach ($agreementsCollection->getItems() as $checkoutAgreement) {
            $checkoutAgreementData[] = [
                AgreementInterface::AGREEMENT_ID => $checkoutAgreement->getAgreementId(),
                AgreementInterface::CONTENT => $checkoutAgreement->getContent(),
                AgreementInterface::NAME => $checkoutAgreement->getName(),
                AgreementInterface::CONTENT_HEIGHT => $checkoutAgreement->getContentHeight(),
                AgreementInterface::CHECKBOX_TEXT => $checkoutAgreement->getCheckboxText(),
                AgreementInterface::IS_HTML => $checkoutAgreement->getIsHtml(),
            ];
        }

        return $checkoutAgreementData;
    }
}

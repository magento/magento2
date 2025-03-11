<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreementsGraphQl\Model\Resolver;

use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checkout Agreements resolver, used for GraphQL request processing
 */
class CheckoutAgreements implements ResolverInterface
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CollectionFactory $agreementCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CollectionFactory $agreementCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->agreementCollectionFactory = $agreementCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE)) {
            return [];
        }

        /** @var Collection $agreementsCollection */
        $agreementsCollection = $this->agreementCollectionFactory->create();
        $agreementsCollection->addStoreFilter($this->storeManager->getStore()->getId());
        $agreementsCollection->addFieldToFilter(AgreementInterface::IS_ACTIVE, 1);

        $checkoutAgreementData = [];
        /** @var AgreementInterface $checkoutAgreement */
        foreach ($agreementsCollection->getItems() as $checkoutAgreement) {
            $checkoutAgreementData[] = [
                AgreementInterface::AGREEMENT_ID => $checkoutAgreement->getAgreementId(),
                AgreementInterface::CONTENT => $checkoutAgreement->getContent(),
                AgreementInterface::NAME => $checkoutAgreement->getName(),
                AgreementInterface::CONTENT_HEIGHT => $checkoutAgreement->getContentHeight(),
                AgreementInterface::CHECKBOX_TEXT => $checkoutAgreement->getCheckboxText(),
                AgreementInterface::IS_HTML => $checkoutAgreement->getIsHtml(),
                AgreementInterface::MODE =>
                    AgreementModeOptions::MODE_AUTO === (int)$checkoutAgreement->getMode() ? 'AUTO' : 'MANUAL',
            ];
        }
        return $checkoutAgreementData;
    }
}

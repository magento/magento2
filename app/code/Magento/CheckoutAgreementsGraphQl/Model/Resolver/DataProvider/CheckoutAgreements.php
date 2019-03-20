<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreementsGraphQl\Model\Resolver\DataProvider;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Checkout Agreements data provider
 */
class CheckoutAgreements
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CheckoutAgreementsListInterface
     */
    private $checkoutAgreementsList;

    /**
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CheckoutAgreementsListInterface $checkoutAgreementsList,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->checkoutAgreementsList = $checkoutAgreementsList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get All Active Checkout Agreements Data
     *
     * @return array
     */
    public function getData(): array
    {
        $this->searchCriteriaBuilder->addFilter(AgreementInterface::IS_ACTIVE, true);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $checkoutAgreements = $this->checkoutAgreementsList->getList($searchCriteria);

        $checkoutAgreementData = [];
        foreach ($checkoutAgreements as $checkoutAgreement) {
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

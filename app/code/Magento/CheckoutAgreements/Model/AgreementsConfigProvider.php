<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;

/**
 * Configuration provider for GiftMessage rendering on "Shipping Method" step of checkout.
 */
class AgreementsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfiguration;

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface
     */
    protected $checkoutAgreementsRepository;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface
     */
    private $checkoutAgreementsList;

    /**
     * @var ActiveStoreAgreementsFilter
     */
    private $activeStoreAgreementsFilter;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface|null $checkoutAgreementsList
     * @param ActiveStoreAgreementsFilter|null $activeStoreAgreementsFilter
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        \Magento\Framework\Escaper $escaper,
        ?\Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface $checkoutAgreementsList = null,
        ?ActiveStoreAgreementsFilter $activeStoreAgreementsFilter = null
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
        $this->escaper = $escaper;
        $this->checkoutAgreementsList = $checkoutAgreementsList ?: ObjectManager::getInstance()->get(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->activeStoreAgreementsFilter = $activeStoreAgreementsFilter ?: ObjectManager::getInstance()->get(
            ActiveStoreAgreementsFilter::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $agreements = [];
        $agreements['checkoutAgreements'] = $this->getAgreementsConfig();

        return $agreements;
    }

    /**
     * Returns agreements config.
     *
     * @return array
     */
    protected function getAgreementsConfig()
    {
        $agreementConfiguration = [];
        $isAgreementsEnabled = $this->scopeConfiguration->isSetFlag(
            AgreementsProvider::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $agreementsList = $this->checkoutAgreementsList->getList(
            $this->activeStoreAgreementsFilter->buildSearchCriteria()
        );
        $agreementConfiguration['isEnabled'] = (bool)($isAgreementsEnabled && count($agreementsList) > 0);

        foreach ($agreementsList as $agreement) {
            $isAgreementHtmlType = $agreement->getIsHtml();
            $agreementConfiguration['agreements'][] = [
                'content' => $isAgreementHtmlType
                    ? $agreement->getContent()
                    : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                'checkboxText' => $isAgreementHtmlType
                    ? $agreement->getCheckboxText()
                    : nl2br($this->escaper->escapeHtml($agreement->getCheckboxText())),
                'mode' => $agreement->getMode(),
                'agreementId' => $agreement->getAgreementId(),
                'contentHeight' => $agreement->getContentHeight()
            ];
        }

        return $agreementConfiguration;
    }
}

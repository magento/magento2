<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

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
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface|null $checkoutAgreementsList
     * @param \Magento\Framework\Api\FilterBuilder|null $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        \Magento\Framework\Escaper $escaper,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface $checkoutAgreementsList = null,
        \Magento\Framework\Api\FilterBuilder $filterBuilder = null,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
        $this->escaper = $escaper;
        $this->checkoutAgreementsList = $checkoutAgreementsList ?: ObjectManager::getInstance()->get(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->filterBuilder = $filterBuilder ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $agreements = [];
        $agreements['checkoutAgreements'] = $this->getAgreementsConfig();
        return $agreements;
    }

    /**
     * Returns agreements config
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

        $storeFilter = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('eq')
            ->setValue($this->storeManager->getStore()->getId())
            ->create();
        $isActiveFilter = $this->filterBuilder
            ->setField('is_active')
            ->setConditionType('eq')
            ->setValue(1)
            ->create();
        $this->searchCriteriaBuilder->addFilters([$storeFilter]);
        $this->searchCriteriaBuilder->addFilters([$isActiveFilter]);

        $agreementsList = $this->checkoutAgreementsList->getList($this->searchCriteriaBuilder->create());
        $agreementConfiguration['isEnabled'] = (bool)($isAgreementsEnabled && count($agreementsList) > 0);

        foreach ($agreementsList as $agreement) {
            $agreementConfiguration['agreements'][] = [
                'content' => $agreement->getIsHtml()
                    ? $agreement->getContent()
                    : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                'checkboxText' => $agreement->getCheckboxText(),
                'mode' => $agreement->getMode(),
                'agreementId' => $agreement->getAgreementId()
            ];
        }

        return $agreementConfiguration;
    }
}

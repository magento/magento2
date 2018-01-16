<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model;

use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection as AgreementCollection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement as AgreementResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Checkout agreement repository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var AgreementResource
     */
    private $resourceModel;

    /**
     * @var AgreementFactory
     */
    private $agreementFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsListingInterface
     */
    private $agreementsListing;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Constructs a checkout agreement data object.
     *
     * @param AgreementCollectionFactory $collectionFactory Collection factory.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager.
     * @param ScopeConfigInterface $scopeConfig Scope config.
     * @param AgreementResource $agreementResource
     * @param AgreementFactory $agreementFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsListingInterface|null $agreementsListing
     * @param \Magento\Framework\Api\FilterBuilder|null $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @codeCoverageIgnore
     */
    public function __construct(
        AgreementCollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AgreementResource $agreementResource,
        AgreementFactory $agreementFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsListingInterface $agreementsListing = null,
        \Magento\Framework\Api\FilterBuilder $filterBuilder = null,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->resourceModel = $agreementResource;
        $this->agreementFactory = $agreementFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->agreementsListing = $agreementsListing ?: ObjectManager::getInstance()->get(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListingInterface::class
        );
        $this->filterBuilder = $filterBuilder ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
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
        return $this->agreementsListing->getListing($this->searchCriteriaBuilder->create());
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\CheckoutAgreements\Api\Data\AgreementInterface $data, $storeId = null)
    {
        $id = $data->getAgreementId();

        if ($id) {
            $data = $this->get($id, $storeId)->addData($data->getData());
        }
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $data->setStores([$storeId]);
        try {
            $this->resourceModel->save($data);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Unable to save checkout agreement %1', $data->getAgreementId())
            );
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\CheckoutAgreements\Api\Data\AgreementInterface $data)
    {
        try {
            $this->resourceModel->delete($data);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Unable to remove checkout agreement %1', $data->getAgreementId())
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $model = $this->get($id);
        $this->delete($model);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, $storeId = null)
    {
        /** @var AgreementFactory $agreement */
        $agreement = $this->agreementFactory->create();
        $this->resourceModel->load($agreement, $id);
        if (!$agreement->getId()) {
            throw new NoSuchEntityException(__('Checkout agreement with specified ID "%1" not found.', $id));
        }
        return $agreement;
    }
}

<?php
namespace Smetana\Third\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Api\SearchResultsInterface;
use Smetana\Third\Api\Data\PartnerInterface;
use Smetana\Third\Api\Data\PartnerSearchResultInterfaceFactory;
use Smetana\Third\Api\PartnerRepositoryInterface;
use Smetana\Third\Model\ResourceModel\Partner as PartnerResource;
use Smetana\Third\Model\ResourceModel\Partner\Collection as PartnerCollection;
use Smetana\Third\Model\ResourceModel\Partner\CollectionFactory as PartnerCollectionFactory;

/**
 * Class Partner repository
 *
 * @package Smetana\Third\Model
 */
class PartnerRepository implements PartnerRepositoryInterface
{
    /**
     * Partners storage
     *
     * @var array
     */
    private $partners = [];

    /**
     * Partner Resource instance
     *
     * @var PartnerResource
     */
    private $partnerResource;

    /**
     * Partner Factory instance
     *
     * @var PartnerFactory
     */
    private $partnerFactory;

    /**
     * Partner Collection Factory instance
     *
     * @var PartnerCollectionFactory
     */
    private $partnerCollectionFactory;

    /**
     * SearchResultInterface instance
     *
     * @var PartnerSearchResultInterfaceFactory
     */
    private $partnerSearchResultFactory;

    /**
     * @param PartnerResource $partnerResource
     * @param PartnerFactory $partnerFactory
     * @param PartnerCollectionFactory $partnerCollectionFactory
     * @param PartnerSearchResultInterfaceFactory $partnerSearchResultFactory
     */
    public function __construct(
        PartnerResource $partnerResource,
        PartnerFactory $partnerFactory,
        PartnerCollectionFactory $partnerCollectionFactory,
        PartnerSearchResultInterfaceFactory $partnerSearchResultFactory
    ) {
        $this->partnerResource = $partnerResource;
        $this->partnerFactory = $partnerFactory;
        $this->partnerCollectionFactory = $partnerCollectionFactory;
        $this->partnerSearchResultFactory = $partnerSearchResultFactory;
    }

    /**
     * Get info about partner by specified id
     *
     * @param int $id
     * @param string $field
     *
     * @return PartnerInterface
     */
    public function getById(int $id, string $field): PartnerInterface
    {
        if (!array_key_exists($id, $this->partners)) {
            /** @var PartnerInterface $partner */
            $partner = $this->partnerFactory->create();
            $this->partnerResource->load($partner, $id, $field);
            if (!$partner->getPartnerId()) {
                return $partner;
            }

            $this->partners[$id] = $partner;
        }

        return $this->partners[$id];
    }

    /**
     * Get partner list
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        /** @var PartnerCollection $collection */
        $collection = $this->partnerCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->partnerSearchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Create partner
     *
     * @param PartnerInterface $partner
     *
     * @return PartnerInterface
     * @throws StateException
     */
    public function save(PartnerInterface $partner): PartnerInterface
    {
        try {
            $this->partnerResource->save($partner);
            $this->partners[$partner->getPartnerId()] = $this->getById(
                $partner->getPartnerId(),
                PartnerInterface::PARTNER_ID
            );
        } catch (\Exception $exception) {
            throw new StateException(__('Unable to save Partner #%1', $partner->getPartnerId()));
        }

        return $this->partners[$partner->getPartnerId()];
    }

    /**
     * Delete partner
     *
     * @param PartnerInterface $partner
     *
     * @return bool
     * @throws StateException
     */
    public function delete(PartnerInterface $partner): bool
    {
        try {
            $this->partnerResource->delete($partner);
            unset($this->partners[$partner->getPartnerId()]);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove Partner #%1', $partner->getPartnerId()));
        }

        return true;
    }

    /**
     * Delete partner by id
     *
     * @param int $partnerId
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById(int $partnerId): bool
    {
        return $this->delete($this->getById($partnerId, PartnerInterface::PARTNER_ID));
    }
}

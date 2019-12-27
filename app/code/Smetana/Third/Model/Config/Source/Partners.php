<?php
namespace Smetana\Third\Model\Config\Source;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Option\ArrayInterface;
use Smetana\Third\Model\PartnerRepository;

/**
 * Partners options class
 *
 * @package Smetana\Third\Model\Config\Source
 */
class Partners implements ArrayInterface
{
    /**
     * Partner repository instance
     *
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * Search criteria instance
     *
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * @param PartnerRepository $partnerRepository
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function __construct(
        PartnerRepository $partnerRepository,
        SearchCriteriaInterface $searchCriteria
    ) {
        $this->partnerRepository = $partnerRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Receive partners data
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $partnerList = [];
        $partners = $this->partnerRepository->getList($this->searchCriteria)->getItems();
        foreach ($partners as $partner) {
            $partnerList[] = ['value' => $partner->getPartnerId(), 'label' => __($partner->getPartnerName())];
        }

        return $partnerList;
    }
}

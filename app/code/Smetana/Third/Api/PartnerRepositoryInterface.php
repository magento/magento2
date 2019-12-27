<?php
namespace Smetana\Third\Api;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\StateException;
use Smetana\Third\Api\Data\PartnerInterface;

/**
 * Interface PartnerRepositoryInterface
 *
 * @package Smetana\Third\Api
 * @api
 */
interface PartnerRepositoryInterface
{
    /**
     * Create partner
     *
     * @param PartnerInterface $partner
     *
     * @return PartnerInterface
     * @throws StateException
     */
    public function save(PartnerInterface $partner): PartnerInterface;

    /**
     * Get info about partner by specified id
     *
     * @param int $id
     * @param string $field
     *
     * @return PartnerInterface
     */
    public function getById(int $id, string $field): PartnerInterface;

    /**
     * Get partner list
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete partner
     *
     * @param PartnerInterface $partner
     *
     * @return bool
     * @throws StateException
     */
    public function delete(PartnerInterface $partner): bool;

    /**
     * Delete partner by id
     *
     * @param int $partnerId
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById(int $partnerId): bool;
}

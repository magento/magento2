<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

use Magento\Framework\Api\SearchCriteria;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseSearchResultsInterface;

/**
 * Signifyd Case repository interface
 *
 * @api
 */
interface CaseRepositoryInterface
{
    /**
     * Saves case entity.
     *
     * @param CaseInterface $case
     * @return CaseInterface
     */
    public function save(CaseInterface $case);

    /**
     * Gets case entity by order id.
     *
     * @param int $id
     * @return CaseInterface
     */
    public function getById($id);

    /**
     * Deletes case entity.
     *
     * @param CaseInterface $case
     * @return bool
     */
    public function delete(CaseInterface $case);

    /**
     * Gets list of case entities.
     *
     * @param SearchCriteria $searchCriteria
     * @return CaseSearchResultsInterface
     */
    public function getList(SearchCriteria $searchCriteria);
}

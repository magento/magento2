<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

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
     * @param \Magento\Signifyd\Api\Data\CaseInterface $case
     * @return \Magento\Signifyd\Api\Data\CaseInterface
     */
    public function save(\Magento\Signifyd\Api\Data\CaseInterface $case);

    /**
     * Gets case entity by order id.
     *
     * @param int $id
     * @return \Magento\Signifyd\Api\Data\CaseInterface
     */
    public function getById($id);

    /**
     * Gets entity by Signifyd case id.
     *
     * @param int $caseId
     * @return \Magento\Signifyd\Api\Data\CaseInterface|null
     */
    public function getByCaseId($caseId);

    /**
     * Deletes case entity.
     *
     * @param \Magento\Signifyd\Api\Data\CaseInterface $case
     * @return bool
     */
    public function delete(\Magento\Signifyd\Api\Data\CaseInterface $case);

    /**
     * Gets list of case entities.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Signifyd\Api\Data\CaseSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}

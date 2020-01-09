<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

/**
 * Signifyd Case repository interface
 *
 * @api
 * @since 100.2.0
 */
interface CaseRepositoryInterface
{
    /**
     * Saves case entity.
     *
     * @param \Magento\Signifyd\Api\Data\CaseInterface $case
     * @return \Magento\Signifyd\Api\Data\CaseInterface
     * @since 100.2.0
     */
    public function save(\Magento\Signifyd\Api\Data\CaseInterface $case);

    /**
     * Gets case entity by order id.
     *
     * @param int $id
     * @return \Magento\Signifyd\Api\Data\CaseInterface
     * @since 100.2.0
     */
    public function getById($id);

    /**
     * Gets entity by Signifyd case id.
     *
     * @param int $caseId
     * @return \Magento\Signifyd\Api\Data\CaseInterface|null
     * @since 100.2.0
     */
    public function getByCaseId($caseId);

    /**
     * Deletes case entity.
     *
     * @param \Magento\Signifyd\Api\Data\CaseInterface $case
     * @return bool
     * @since 100.2.0
     */
    public function delete(\Magento\Signifyd\Api\Data\CaseInterface $case);

    /**
     * Gets list of case entities.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Signifyd\Api\Data\CaseSearchResultsInterface
     * @since 100.2.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}

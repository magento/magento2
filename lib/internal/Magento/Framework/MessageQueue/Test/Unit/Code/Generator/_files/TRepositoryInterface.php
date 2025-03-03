<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Code\Generator;

/**
 * Stub interface for remote service generator with PHP 7.0 syntax.
 */
interface TRepositoryInterface
{
    /**
     * Saves TInterface entity.
     *
     * @param TInterface $t
     * @return \Magento\Framework\MessageQueue\Code\Generator\TInterface
     */
    public function save(\Magento\Framework\MessageQueue\Code\Generator\TInterface $t)
        : \Magento\Framework\MessageQueue\Code\Generator\TInterface;

    /**
     * Retrieves TInterfaces entity.
     *
     * @param string $attribute
     * @param int|null $typeId
     * @return TInterface
     */
    public function get(string $attribute, ?int $typeId = null)
        : \Magento\Framework\MessageQueue\Code\Generator\TInterface;

    /**
     * Retrieves TInterface entity by id.
     *
     * @param int $tId
     * @return TInterface
     */
    public function getById(int $tId) : \Magento\Framework\MessageQueue\Code\Generator\TInterface;

    /**
     * Gets list of TInterface entities.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\MessageQueue\Code\Generator\TSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Deletes TInterface entity.
     *
     * @param TInterface $t
     * @return bool
     */
    public function delete(\Magento\Framework\MessageQueue\Code\Generator\TInterface $t) : bool;

    /**
     * Deletes TInterface entity by id.
     *
     * @param int $tId
     * @return bool
     */
    public function deleteById(int $tId) : bool;
}

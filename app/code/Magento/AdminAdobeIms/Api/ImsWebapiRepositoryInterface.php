<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api;

use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterface;

use Magento\AdminAdobeIms\Api\Data\ImsWebapiSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Declare ims web api repository
 * @api
 */
interface ImsWebapiRepositoryInterface
{
    /**
     * Save ims token
     *
     * @param ImsWebapiInterface $entity
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(ImsWebapiInterface $entity): void;

    /**
     * Get ims token
     *
     * @param int $entityId
     * @return ImsWebapiInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ImsWebapiInterface;

    /**
     * Get ims token(s) by admin user id
     *
     * @param int $adminUserId
     * @return ImsWebapiInterface[]
     * @throws NoSuchEntityException
     */
    public function getByAdminUserId(int $adminUserId): array;

    /**
     * Get entity by access token hash
     *
     * @param string $tokenHash
     * @return ImsWebapiInterface
     * @throws NoSuchEntityException
     */
    public function getByAccessTokenHash(string $tokenHash): ImsWebapiInterface;

    /**
     * Get ims token by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ImsWebapiSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ImsWebapiSearchResultsInterface;

    /**
     * Delete ims tokens for admin user id.
     *
     * @param int $adminUserId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteByAdminUserId(int $adminUserId): bool;
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk;

use Magento\AsynchronousOperations\Model\GetGlobalAllowedUserTypes;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 *  DataProvider for Bulk operations list
 */
class DataProvider extends AbstractDataProvider
{
    private const BULK_LOGGING_ACL = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations";

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var GetGlobalAllowedUserTypes
     */
    private $getGlobalAllowedUserTypes;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param AuthorizationInterface $authorization
     * @param GetGlobalAllowedUserTypes $getGlobalAllowedUserTypes
     * @param UserContextInterface $userContext
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        AuthorizationInterface $authorization,
        GetGlobalAllowedUserTypes $getGlobalAllowedUserTypes,
        UserContextInterface $userContext,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->authorization = $authorization;
        $this->getGlobalAllowedUserTypes = $getGlobalAllowedUserTypes;
        $this->userContext = $userContext;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data for Bulk Operations Grid
     *
     * @return array
     */
    public function getData(): array
    {
        $allowedUserTypes = $this->getGlobalAllowedUserTypes->execute();
        $connection = $this->getCollection()->getConnection();

        $whereOr = [];
        if ($allowedUserTypes) {
            $whereOr[] = $connection->quoteInto('user_type IN (?)', $allowedUserTypes);
        }

        if ($this->isAllowed()) {
            $whereOr[] = implode(
                ' AND ',
                [
                    $connection->quoteInto('user_type = ?', $this->userContext->getUserType()),
                    $connection->quoteInto('user_id = ?', $this->userContext->getUserId())
                ]
            );
        }

        $this->getCollection()
            ->getSelect()
            ->where('(' . implode(') OR (', $whereOr) . ')');

        return $this->getCollection()->toArray();
    }

    /**
     * Check if it allowed to see own bulk operations.
     *
     * @return bool
     */
    private function isAllowed(): bool
    {
        return $this->authorization->isAllowed(self::BULK_LOGGING_ACL);
    }
}

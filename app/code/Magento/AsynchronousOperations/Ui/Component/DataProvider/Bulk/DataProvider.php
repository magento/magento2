<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk;

use Magento\AsynchronousOperations\Model\AccessManager;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 *  DataProvider for Bulk operations list
 */
class DataProvider extends AbstractDataProvider
{

    /**
     * @var AccessManager
     */
    private $accessManager;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param AccessManager $accessManager
     * @param FilterBuilder $filterBuilder
     * @param UserContextInterface $userContext
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        AccessManager $accessManager,
        FilterBuilder $filterBuilder,
        UserContextInterface $userContext,
        array $meta = [],
        array $data = []
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->accessManager = $accessManager;
        $this->userContext = $userContext;
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data for Bulk Operations Grid
     *
     * @return array
     */
    public function getData()
    {
        $allowedUserTypes = $this->accessManager->getGlobalAllowedUserTypes();
        $connection = $this->getCollection()->getConnection();
        $whereOr = [];
        if (count($allowedUserTypes) > 0) {
            $whereOr[] = $connection->quoteInto("user_type IN(?)", $allowedUserTypes);
        }

        if ($this->accessManager->isOwnActionsAllowed()) {
            $whereOr[] = implode(
                ' AND ',
                [
                    $connection->quoteInto('user_type = ?', $this->userContext->getUserType()),
                    $connection->quoteInto('user_id = ?', $this->userContext->getUserId())
                ]
            );
        }

        $whereCond = '(' . implode(') OR (', $whereOr) . ')';
        $this->getCollection()->getSelect()->where($whereCond);

        return $this->getCollection()->toArray();
    }
}

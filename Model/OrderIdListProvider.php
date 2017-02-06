<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Sales\Model\ResourceModel\Provider\IdListProviderInterface;
use Magento\Signifyd\Model\ResourceModel\CaseEntity;

/**
 * Provides order ids list which Signifyd Case guaranty status were changed
 */
class OrderIdListProvider implements IdListProviderInterface
{
    /**
     * @var ResourceModel\CaseEntity
     */
    private $caseEntity;

    /**
     * @param ResourceModel\CaseEntity $caseEntity
     */
    public function __construct(
        CaseEntity $caseEntity
    ) {
        $this->caseEntity = $caseEntity;
    }

    /**
     * @inheritdoc
     */
    public function get($mainTableName, $gridTableName)
    {
        $connection = $this->caseEntity->getConnection();
        $select = $connection->select()
            ->from($this->caseEntity->getMainTable(), ['order_id'])
            ->joinLeft(
                [$gridTableName => $connection->getTableName($gridTableName)],
                sprintf(
                    '%s.%s = %s.%s',
                    $this->caseEntity->getMainTable(),
                    'order_id',
                    $gridTableName,
                    'entity_id'
                ),
                []
            )
            ->where('guarantee_disposition != signifyd_guarantee_status');

        return $connection->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }
}

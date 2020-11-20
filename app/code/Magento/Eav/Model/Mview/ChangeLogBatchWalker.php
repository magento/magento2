<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Mview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Mview\View\ChangeLogBatchWalkerInterface;
use Magento\Framework\Mview\View\ChangelogInterface;

/**
 * Class BatchIterator
 */
class ChangeLogBatchWalker implements ChangeLogBatchWalkerInterface
{
    private const GROUP_CONCAT_MAX_VARIABLE = 'group_concat_max_len';
    /** ID is defined as small int. Default size of it is 5 */
    private const DEFAULT_ID_SIZE = 5;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $entityTypeCodes;

    /**
     * @param ResourceConnection $resourceConnection
     * @param array $entityTypeCodes
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        array $entityTypeCodes = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->entityTypeCodes = $entityTypeCodes;
    }

    /**
     * Calculate EAV attributes size
     *
     * @param ChangelogInterface $changelogData
     * @return int
     * @throws \Exception
     */
    private function calculateEavAttributeSize(ChangelogInterface $changelogData): int
    {
        $connection = $this->resourceConnection->getConnection();

        if (!isset($this->entityTypeCodes[$changelogData->getViewId()])) {
            throw new \Exception('Entity type for view was not defined');
        }

        $select = $connection->select();
        $select->from(
            $this->resourceConnection->getTableName('eav_attribute'),
            new Expression('COUNT(*)')
        )
            ->joinInner(
              ['type' => $connection->getTableName('eav_entity_type')],
                'type.entity_type_id=eav_attribute.entity_type_id'
            )
            ->where('type.entity_type_code = ?', $this->entityTypeCodes[$changelogData->getViewId()]);

        return (int) $connection->fetchOne($select);
    }

    /**
     * Prepare group max concat
     *
     * @param int $numberOfAttributes
     * @return void
     * @throws \Exception
     */
    private function setGroupConcatMax(int $numberOfAttributes): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->query(sprintf(
            'SET SESSION %s=%s',
            self::GROUP_CONCAT_MAX_VARIABLE,
            $numberOfAttributes * (self::DEFAULT_ID_SIZE + 1)
        ));
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function walk(ChangelogInterface $changelogData, int $fromVersionId, int $toVersion, int $batchSize)
    {
        $connection = $this->resourceConnection->getConnection();
        $numberOfAttributes = $this->calculateEavAttributeSize($changelogData);
        $this->setGroupConcatMax($numberOfAttributes);
        $select = $connection->select()->distinct(true)
            ->where(
                'version_id > ?',
                (int) $fromVersionId
            )
            ->where(
                'version_id <= ?',
                $toVersion
            )
            ->group([$changelogData->getColumnName(), 'store_id'])
            ->limit($batchSize);

        $columns = [
            $changelogData->getColumnName(),
            'attribute_ids' => new Expression('GROUP_CONCAT(attribute_id)'),
            'store_id'
        ];
        $select->from($changelogData->getName(), $columns);
        return $connection->fetchAll($select);
    }
}

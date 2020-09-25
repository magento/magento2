<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Mview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Exception\LocalizedException;
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
     * @var ChangelogInterface
     */
    private $changelog;
    /**
     * @var int
     */
    private $fromVersionId;

    /**
     * @var int
     */
    private $toVersionId;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ChangelogInterface $changelog
     * @param int $fromVersionId
     * @param int $toVersionId
     * @param int $batchSize
     * @param array $entityTypeCodes
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ChangelogInterface $changelog,
        int $fromVersionId,
        int $toVersionId,
        int $batchSize,
        array $entityTypeCodes = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->entityTypeCodes = $entityTypeCodes;
        $this->changelog = $changelog;
        $this->fromVersionId = $fromVersionId;
        $this->toVersionId = $toVersionId;
        $this->batchSize = $batchSize;
    }

    /**
     * @return \Generator|\Traversable
     * @throws \Exception
     */
    public function getIterator(): \Generator
    {
        while ($this->fromVersionId < $this->toVersionId) {
            $ids = $this->walk();

            if (empty($ids)) {
                break;
            }
            $this->fromVersionId += $this->batchSize;
            yield $ids;
        }
    }

    /**
     * Calculate EAV attributes size
     *
     * @param ChangelogInterface $changelog
     * @return int
     * @throws LocalizedException
     */
    private function calculateEavAttributeSize(): int
    {
        $connection = $this->resourceConnection->getConnection();

        if (!isset($this->entityTypeCodes[$this->changelog->getViewId()])) {
            throw new LocalizedException(__('Entity type for view was not defined'));
        }

        $select = $connection->select();
        $select->from(
            $this->resourceConnection->getTableName('eav_attribute'),
            new Expression('COUNT(*)')
        )->joinInner(
            ['type' => $connection->getTableName('eav_entity_type')],
                'type.entity_type_id=eav_attribute.entity_type_id'
        )->where('type.entity_type_code = ?', $this->entityTypeCodes[$this->changelog->getViewId()]);

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
    private function walk()
    {
        $connection = $this->resourceConnection->getConnection();
        $numberOfAttributes = $this->calculateEavAttributeSize();
        $this->setGroupConcatMax($numberOfAttributes);
        $select = $connection->select()->distinct(true)
            ->where(
                'version_id > ?',
                (int) $this->fromVersionId
            )
            ->where(
                'version_id <= ?',
                $this->toVersionId
            )
            ->group([$this->changelog->getColumnName(), 'store_id'])
            ->limit($this->batchSize);

        $columns = [
            $this->changelog->getColumnName(),
            'attribute_ids' => new Expression('GROUP_CONCAT(attribute_id)'),
            'store_id'
        ];
        $select->from($this->changelog->getName(), $columns);
        return $connection->fetchAll($select);
    }
}

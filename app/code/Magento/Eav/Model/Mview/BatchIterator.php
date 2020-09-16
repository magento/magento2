<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Mview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Mview\View\ChangeLogBatchIteratorInterface;
use Magento\Framework\Mview\View\ChangelogInterface;
use Magento\Framework\Mview\View\ChangelogTableNotExistsException;
use Magento\Framework\Phrase;

/**
 * Class BatchIterator
 */
class BatchIterator implements ChangeLogBatchIteratorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function walk(ChangelogInterface $changelog, int $fromVersionId, int $toVersion, int $batchSize)
    {
        $connection = $this->resourceConnection->getConnection();
        if (!$connection->isTableExists($changelog->getName())) {
            throw new ChangelogTableNotExistsException(
                new Phrase("Table %1 does not exist", [$changelog->getName()])
            );
        }
        $select = $connection->select()->distinct(true)
            ->where(
                'version_id > ?',
                (int)$fromVersionId
            )
            ->where(
                'version_id <= ?',
                $toVersion
            )
            ->group([$changelog->getColumnName(), 'store_id'])
            ->limit($batchSize);

        $columns = [
            $changelog->getColumnName(),
            'attribute_ids' => new Expression('GROUP_CONCAT(attribute_id)'),
            'store_id'
        ];

        $select->from($changelog->getName(), $columns);
        return $connection->fetchAll($select);
    }
}

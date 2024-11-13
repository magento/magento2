<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Db;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
use Magento\SalesSequence\Model\EntityPool;

class Sequence
{
    /**
     * @var AppResource
     */
    protected $appResource;

    /**
     * @var DdlSequence
     */
    protected $ddlSequence;

    /**
     * @var EntityPool
     */
    private $entityPool;

    /**
     * @param AppResource $appResource
     * @param DdlSequence $ddlSequence
     * @param EntityPool $entityPool
     */
    public function __construct(
        AppResource $appResource,
        DdlSequence $ddlSequence,
        EntityPool $entityPool
    ) {
        $this->appResource = $appResource;
        $this->ddlSequence = $ddlSequence;
        $this->entityPool = $entityPool;
    }

    /**
     * Generates sequence for store IDS 0..(n-1)
     *
     * @param int $n
     * @return void
     */
    public function generateSequences($n = 10)
    {
        for ($i = 0; $i < $n; $i++) {
            $this->generate($i);
        }
    }

    /**
     * Generates sequence for store ID
     *
     * @param int $storeId
     * @return void
     */
    public function generate(int $storeId): void
    {
        $connection = $this->appResource->getConnection();
        foreach ($this->entityPool->getEntities() as $entityName) {
            $sequenceName = $this->appResource->getTableName(sprintf('sequence_%s_%s', $entityName, $storeId));
            if (!$connection->isTableExists($sequenceName)) {
                $connection->query($this->ddlSequence->getCreateSequenceDdl($sequenceName));
            }
        }
    }
}

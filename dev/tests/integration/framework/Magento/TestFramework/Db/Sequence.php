<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Db;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;

/**
 * Class Sequence
 */
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
     * @var array
     */
    protected $entities = [
        'order',
        'invoice',
        'shipment',
        'rma_item'
    ];

    /**
     * @param AppResource $appResource
     * @param DdlSequence $ddlSequence
     */
    public function __construct(
        AppResource $appResource,
        DdlSequence $ddlSequence
    ) {
        $this->appResource = $appResource;
        $this->ddlSequence = $ddlSequence;
    }

    /**
     * @param int $n
     * @return void
     */
    public function generateSequences($n = 10)
    {
        $connection = $this->appResource->getConnection();
        for ($i = 0; $i < $n; $i++) {
            foreach ($this->entities as $entityName) {
                $sequenceName = $this->appResource->getTableName(sprintf('sequence_%s_%s', $entityName, $i));
                if (!$connection->isTableExists($sequenceName)) {
                    $connection->query($this->ddlSequence->getCreateSequenceDdl($sequenceName));
                }
            }
        }
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model\ResourceModel;

use Magento\MessageQueue\Api\PoisonPillReadInterface;
use Magento\MessageQueue\Api\PoisonPillPutInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * PoisonPill.
 */
class PoisonPill extends AbstractDb implements PoisonPillPutInterface, PoisonPillReadInterface
{
    /**
     * Table name.
     */
    const QUEUE_POISON_PILL_TABLE = 'queue_poison_pill';

    /**
     * PoisonPill constructor.
     *
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::QUEUE_POISON_PILL_TABLE, 'version');
    }

    /**
     * @inheritdoc
     */
    public function put(): int
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $connection->insert($table, []);
        return (int)$connection->lastInsertId($table);
    }

    /**
     * @inheritdoc
     */
    public function getLatestVersion() : int
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::QUEUE_POISON_PILL_TABLE),
            'version'
        )->order(
            'version ' . \Magento\Framework\DB\Select::SQL_DESC
        )->limit(
            1
        );

        $version = (int)$this->getConnection()->fetchOne($select);

        return $version;
    }
}

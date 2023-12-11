<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model\ResourceModel;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * PoisonPill class that enclose read and put interface.
 */
class PoisonPill extends AbstractDb implements PoisonPillPutInterface, PoisonPillReadInterface
{
    /**
     * Table name.
     */
    const QUEUE_POISON_PILL_TABLE = 'queue_poison_pill';

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
    public function put(): string
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $uuid = uniqid('version-');
        $version = $this->getVersionFromDb();
        if ($version !== '') {
            $connection->update($table, ['version' => $uuid]);
        } else {
            $connection->insert($table, ['version' => $uuid]);
        }

        return $uuid;
    }

    /**
     * @inheritdoc
     */
    public function getLatestVersion(): string
    {
        return $this->getVersionFromDb();
    }

    /**
     * Returns version form DB or null.
     *
     * @return string
     */
    private function getVersionFromDb(): string
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::QUEUE_POISON_PILL_TABLE),
            'version'
        );

        $result = $this->getConnection()->fetchOne($select);

        return (string)$result;
    }
}

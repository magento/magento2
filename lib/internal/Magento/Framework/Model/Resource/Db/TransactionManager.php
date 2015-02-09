<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Db;

use Magento\Framework\DB\Adapter\AdapterInterface as Connection;

class TransactionManager implements TransactionManagerInterface
{
    const STATE_IN_PROGRESS = 'in progress';
    const STATE_FINISHED = 'finished';

    /**
     * @var array
     */
    protected $participants;

    /**
     * {@inheritdoc}
     */
    public function start(Connection $connection)
    {
        $key = $this->getConnectionKey($connection);
        if (!isset($this->participants[$key])) {
            $this->participants[$key]['item'] = $connection;
            $this->participants[$key]['state'] = self::STATE_IN_PROGRESS;
            $connection->beginTransaction();
        }
        return $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function end(Connection $connection)
    {
        $key = $this->getConnectionKey($connection);
        $this->participants[$key]['state'] = self::STATE_FINISHED;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        foreach ($this->participants as $item) {
            if ($item['state'] != self::STATE_FINISHED) {
                throw new \Exception('Incomplete transactions. Cannot update row: a foreign key constraint fails');
            }
        }

        foreach ($this->participants as $item) {
            /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
            $connection = $item['item'];
            $connection->commit();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        foreach ($this->participants as $item) {
            /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
            $connection = $item['item'];
            $connection->rollBack();
        }
    }

    /**
     * Get object key
     *
     * @param Connection $connection
     * @return string
     */
    protected function getConnectionKey(Connection $connection)
    {
        return spl_object_hash($connection);
    }
}

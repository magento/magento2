<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * DB transaction model
 *
 * @todo need collect connection by name
 *
 * @api
 */
class Transaction
{
    /**
     * Objects which will be involved to transaction
     *
     * @var array
     */
    protected $objects = [];

    /**
     * Transaction objects array with alias key
     *
     * @var array
     */
    private $objectsByAlias = [];

    /**
     * Callbacks array.
     *
     * @var array
     */
    private $beforeCommitCallbacks = [];

    /**
     * Begin transaction for all involved object resources
     *
     * @return $this
     */
    private function startTransaction()
    {
        foreach ($this->objects as $object) {
            $object->getResource()->beginTransaction();
        }
        return $this;
    }

    /**
     * Commit transaction for all resources
     *
     * @return $this
     */
    private function commitTransaction()
    {
        foreach ($this->objects as $object) {
            $object->getResource()->commit();
        }
        return $this;
    }

    /**
     * Rollback transaction
     *
     * @return $this
     */
    private function rollbackTransaction()
    {
        foreach ($this->objects as $object) {
            $object->getResource()->rollBack();
        }
        return $this;
    }

    /**
     * Run all configured object callbacks
     *
     * @return $this
     */
    private function runCallbacks()
    {
        foreach ($this->beforeCommitCallbacks as $callback) {
            call_user_func($callback);
        }
        return $this;
    }

    /**
     * Adding object for using in transaction
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $alias
     * @return $this
     */
    public function addObject(\Magento\Framework\Model\AbstractModel $object, $alias = '')
    {
        $this->objects[] = $object;
        if (!empty($alias)) {
            $this->objectsByAlias[$alias] = $object;
        }
        return $this;
    }

    /**
     * Add callback function which will be called before commit transactions
     *
     * @param callable $callback
     * @return $this
     */
    public function addCommitCallback($callback)
    {
        $this->beforeCommitCallbacks[] = $callback;
        return $this;
    }

    /**
     * Initialize objects save transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $this->startTransaction();
        $error = false;

        try {
            foreach ($this->objects as $object) {
                $object->save();
            }
        } catch (\Exception $e) {
            $error = $e;
        }

        if ($error === false) {
            try {
                $this->runCallbacks();
            } catch (\Exception $e) {
                $error = $e;
            }
        }

        if ($error) {
            $this->rollbackTransaction();
            throw $error;
        } else {
            $this->commitTransaction();
        }

        return $this;
    }

    /**
     * Initialize objects delete transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function delete()
    {
        $this->startTransaction();
        $error = false;

        try {
            foreach ($this->objects as $object) {
                $object->delete();
            }
        } catch (\Exception $e) {
            $error = $e;
        }

        if ($error === false) {
            try {
                $this->runCallbacks();
            } catch (\Exception $e) {
                $error = $e;
            }
        }

        if ($error) {
            $this->rollbackTransaction();
            throw $error;
        } else {
            $this->commitTransaction();
        }
        return $this;
    }
}

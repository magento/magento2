<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

class Process
{
    /** @var int */
    private $pid;

    /** @var null|int */
    private $status;

    /** @var callable */
    private $handler;

    /**
     * @param callable $handler
     */
    public function __construct($handler)
    {
        $this->pid = 0;
        $this->status = null;
        $this->handler = $handler;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     * @return void
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function run()
    {
        $status = call_user_func($this->handler, $this);

        $status = is_integer($status) ? $status : 0;
        exit($status);
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        $pid = pcntl_waitpid($this->getPid(), $status, WNOHANG);
        if ($pid == -1 || $pid === $this->getPid()) {
            $this->status = pcntl_wexitstatus($status);
            return true;
        }
        return false;
    }

    /**
     * @return int|null
     */
    public function getStatus()
    {
        return $this->status;
    }
}

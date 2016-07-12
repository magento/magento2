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
    private $callable;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->pid = 0;
        $this->status = null;
        $this->callable = $callable;
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
     */
    public function run()
    {
        $status = call_user_func($this->callable, $this);

        $status = is_integer($status) ? $status : 0;
        exit($status);
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        $pid = pcntl_waitpid($this->getPid(), $status, WNOHANG);
        if($pid == -1 || $pid === $this->getPid()) {
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

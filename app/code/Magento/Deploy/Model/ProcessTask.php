<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

class ProcessTask
{
    /**
     * @var string
     */
    private $taskId;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var array
     */
    private $dependentTasks;

    /**
     * @param callable $handler
     * @param array $dependentTasks
     */
    public function __construct($handler, array $dependentTasks = [])
    {
        $this->taskId = uniqid('', true);
        $this->handler = $handler;
        $this->dependentTasks = $dependentTasks;
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->taskId;
    }

    /**
     * @return ProcessTask[]
     */
    public function getDependentTasks()
    {
        return $this->dependentTasks;
    }
}

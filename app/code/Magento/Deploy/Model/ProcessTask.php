<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

/**
 * Class ProcessTask
 *
 * @deprecated since 2.2.0
 */
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

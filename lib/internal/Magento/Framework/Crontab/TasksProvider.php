<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

/**
 * TasksProvider collects list of tasks
 * @since 2.2.0
 */
class TasksProvider implements TasksProviderInterface
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $tasks = [];

    /**
     * @param array $tasks
     * @since 2.2.0
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}

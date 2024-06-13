<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Crontab;

/**
 * TasksProvider collects list of tasks
 */
class TasksProvider implements TasksProviderInterface
{
    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @param array $tasks
     */
    public function __construct(array $tasks = [])
    {
        $this->tasks = $tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}

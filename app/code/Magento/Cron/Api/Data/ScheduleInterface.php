<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cron\Api\Data;

interface ScheduleInterface
{
    /**
     * Constants for keys of data array
     */
    const SCHEDULE_ID       = 'schedule_id';
    const JOB_CODE          = 'job_code';
    const STATUS            = 'status';
    const MESSAGES          = 'messages';
    const CREATED_AT        = 'created_at';
    const SCHEDULED_AT      = 'scheduled_at';
    const EXECUTED_AT       = 'executed_at';
    const FINISHED_AT       = 'finished_at';

    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     * @param int $id
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setId($id);

    /**
     * Get Job Code
     * @return string|null
     */
    public function getJobCode();

    /**
     * Set Job Code
     * @param string $jobCode
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setJobCode($jobCode);

    /**
     * Get Status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set Status
     * @param string $status
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setStatus($status);

    /**
     * Get messages
     * @return string|null
     */
    public function getMessages();

    /**
     * Set messages
     * @param string $messages
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setMessages($messages);

    /**
     * Get created at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at
     * @param string $createdAt
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get scheduled at
     * @return string|null
     */
    public function getScheduledAt();

    /**
     * Set scheduled at
     * @param string $scheduledAt
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setScheduledAt($scheduledAt);

    /**
     * Get executed at
     * @return string|null
     */
    public function getExecutedAt();

    /**
     * Set executed at
     * @param string $executedAt
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setExecutedAt($executedAt);

    /**
     * Get finished at
     * @return string|null
     */
    public function getFinishedAt();

    /**
     * Set finished at
     * @param string $finishedAt
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setFinishedAt($finishedAt);
}

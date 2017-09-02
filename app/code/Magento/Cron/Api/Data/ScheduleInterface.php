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
     * @param string $job_code
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setJobCode($job_code);

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
     * @param string $created_at
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setCreatedAt($created_at);

    /**
     * Get scheduled at
     * @return string|null
     */
    public function getScheduledAt();

    /**
     * Set scheduled at
     * @param string $scheduled_at
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setScheduledAt($scheduled_at);
    
    /**
     * Get executed at
     * @return string|null
     */
    public function getExecutedAt();
    
    /**
     * Set executed at
     * @param string $executed_at
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setExecutedAt($executed_at);

    /**
     * Get finished at
     * @return string|null
     */
    public function getFinishedAt();
    
    /**
     * Set finished at
     * @param string $finished_at
     * @return \Magento\Cron\Api\Data\ScheduleInterface
     */
    public function setFinishedAt($finished_at);
}

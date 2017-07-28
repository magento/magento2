<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

/**
 * Job Queue
 * @since 2.0.0
 */
class Queue
{
    /**#@+
     * Key used in queue file.
     */
    const KEY_JOBS = 'jobs';
    const KEY_JOB_NAME = 'name';
    const KEY_JOB_PARAMS = 'params';
    /**#@-*/

    /**
     * @var Queue\Reader
     * @since 2.0.0
     */
    protected $reader;

    /**
     * @var Queue\Writer
     * @since 2.0.0
     */
    protected $writer;

    /**
     * @var JobFactory
     * @since 2.0.0
     */
    protected $jobFactory;

    /**
     * Initialize dependencies.
     *
     * @param Queue\Reader $reader
     * @param Queue\Writer $writer
     * @param JobFactory $jobFactory
     * @since 2.0.0
     */
    public function __construct(Queue\Reader $reader, Queue\Writer $writer, JobFactory $jobFactory)
    {
        $this->reader = $reader;
        $this->jobFactory = $jobFactory;
        $this->writer = $writer;
    }

    /**
     * Peek at job queue
     *
     * @return array
     * @since 2.0.0
     */
    public function peek()
    {
        $queue = json_decode($this->reader->read(), true);
        if (!is_array($queue)) {
            return [];
        }
        if (isset($queue[self::KEY_JOBS]) && is_array($queue[self::KEY_JOBS])) {
            $this->validateJobDeclaration($queue[self::KEY_JOBS][0]);
            return $queue[self::KEY_JOBS][0];
        } else {
            throw new \RuntimeException(sprintf('"%s" field is missing or is not an array.', self::KEY_JOBS));
        }
    }

    /**
     * Pop job queue.
     *
     * @return AbstractJob|null
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function popQueuedJob()
    {
        $job = null;
        $queue = json_decode($this->reader->read(), true);
        if (!is_array($queue)) {
            return $job;
        }
        if (isset($queue[self::KEY_JOBS]) && is_array($queue[self::KEY_JOBS])) {
            $this->validateJobDeclaration($queue[self::KEY_JOBS][0]);
            $job = $this->jobFactory->create(
                $queue[self::KEY_JOBS][0][self::KEY_JOB_NAME],
                $queue[self::KEY_JOBS][0][self::KEY_JOB_PARAMS]
            );
            array_shift($queue[self::KEY_JOBS]);
            if (empty($queue[self::KEY_JOBS])) {
                $this->writer->write('');
            } else {
                $this->writer->write(json_encode($queue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            throw new \RuntimeException(sprintf('"%s" field is missing or is not an array.', self::KEY_JOBS));
        }
        return $job;
    }

    /**
     * Returns if job queue is empty
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEmpty()
    {
        $queue = json_decode($this->reader->read(), true);
        return empty($queue);
    }

    /**
     * @param array $jobs
     * @return void
     * @since 2.0.0
     */
    public function addJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->validateJobDeclaration($job);
            $queue = json_decode($this->reader->read(), true);
            $queue[self::KEY_JOBS][] = $job;
            $this->writer->write(json_encode($queue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    /**
     * Make sure job declaration is correct.
     *
     * @param object $job
     * @return void
     * @throws \RuntimeException
     * @since 2.0.0
     */
    protected function validateJobDeclaration($job)
    {
        $requiredFields = [self::KEY_JOB_NAME, self::KEY_JOB_PARAMS];
        foreach ($requiredFields as $field) {
            if (!isset($job[$field])) {
                throw new \RuntimeException(sprintf('"%s" field is missing for one or more jobs.', $field));
            }
        }
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess\Process;

/**
 * Describing a process.
 */
class Data
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string[]
     */
    private $dependsOn;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $id
     * @param string[] $dependsOn
     * @param array $data
     */
    public function __construct(string $id, array $data, array $dependsOn = [])
    {
        $this->id = $id;
        $this->dependsOn = $dependsOn;
        $this->data = $data;
    }

    /**
     * Unique ID of the process.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Unique IDs of processes this one depends on.
     *
     * @return string[]
     */
    public function getDependsOn(): array
    {
        return $this->dependsOn;
    }

    /**
     * Data to be passed to process runner.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}

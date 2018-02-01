<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess\Process;


/**
 * A process(es) exited with an error.
 */
class ExitedWithErrorException extends \RuntimeException
{
    /**
     * @var Data[]
     */
    private $processes;

    /**
     * @param Data[] $processes
     */
    public function __construct(array $processes) {
        parent::__construct();
        $this->processes = $processes;
    }

    /**
     * @return Data[]
     */
    public function getFailedProcesses(): array
    {
        return $this->processes;
    }
}

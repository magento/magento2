<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ParallelProcess\Process;

/**
 * Executes code inside a certain process.
 */
interface RunnerInterface
{
    /**
     * Perform actions based on given data.
     *
     * @param array $data
     *
     * @return void
     */
    public function run(array $data);
}

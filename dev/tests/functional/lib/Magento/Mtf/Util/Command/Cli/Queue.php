<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

/**
 * Class Queue
 */
class Queue extends \Magento\Mtf\Util\Command\Cli
{
    /**
     * Starts consumer
     *
     * @param string $consumer
     */
    public function run($consumer)
    {
        parent::execute('queue:consumers:start ' . $consumer . ' > /dev/null &');
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Queue;

/**
 * Queue content writer
 */
class Writer extends Reader
{
    /**
     * Write JSON string into queue
     *
     * @param $data
     * @return void
     */
    public function write($data)
    {
        file_put_contents($this->queueFilePath, $data);
    }
}

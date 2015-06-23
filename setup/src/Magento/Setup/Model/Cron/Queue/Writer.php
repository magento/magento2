<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Queue;

class Writer extends Reader
{
    public function write($data)
    {
        file_put_contents($this->queueFilePath, $data);
    }
}

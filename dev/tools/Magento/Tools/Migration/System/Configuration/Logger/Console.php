<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Logger;

/**
 * Migration logger. Output result print to console
 */
class Console extends \Magento\Tools\Migration\System\Configuration\AbstractLogger
{
    /**
     * Print logs to console
     *
     * @return void
     */
    public function report()
    {
        echo $this;
    }
}

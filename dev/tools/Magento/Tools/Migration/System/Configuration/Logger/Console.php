<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

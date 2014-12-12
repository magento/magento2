<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Migration\Acl\Db\Logger;

/**
 * Db migration logger. Output result print to console
 */
class Console extends \Magento\Tools\Migration\Acl\Db\AbstractLogger
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

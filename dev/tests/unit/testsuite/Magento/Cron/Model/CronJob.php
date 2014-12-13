<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Class CronJob used to check that cron can execute method and pass param
 * Please see \Magento\Cron\Model\ObserverTest
 */
namespace Magento\Cron\Model;

class CronJob
{
    protected $_param;

    public function execute($param)
    {
        $this->_param = $param;
    }

    public function getParam()
    {
        return $this->_param;
    }
}

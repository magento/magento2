<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

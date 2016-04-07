<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

class Quiet implements \Magento\Framework\DB\LoggerInterface
{
    /**
     * {@inheritdoc}
     */
    public function log($str)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function critical(\Exception $e)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startTimer()
    {
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

/**
 * Class \Magento\Framework\DB\Logger\Quiet
 *
 * @since 2.0.0
 */
class Quiet implements \Magento\Framework\DB\LoggerInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function log($str)
    {
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function critical(\Exception $e)
    {
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function startTimer()
    {
    }
}

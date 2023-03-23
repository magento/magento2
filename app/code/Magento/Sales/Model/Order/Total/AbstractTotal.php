<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Total;

/**
 * Base class for configure totals order
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 *
 * @since 100.0.2
 */
abstract class AbstractTotal extends \Magento\Framework\DataObject
{
    /**
     * Process model configuration array.
     *
     * This method can be used for changing models apply sort order
     *
     * @param   array $config
     * @return  array
     */
    public function processConfigArray($config)
    {
        return $config;
    }
}

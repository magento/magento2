<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Total;

/**
 * Base class for configure totals order
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class AbstractTotal extends \Magento\Framework\DataObject
{
    /**
     * Process model configuration array.
     * This method can be used for changing models apply sort order
     *
     * @param   array $config
     * @return  array
     * @since 2.0.0
     */
    public function processConfigArray($config)
    {
        return $config;
    }
}

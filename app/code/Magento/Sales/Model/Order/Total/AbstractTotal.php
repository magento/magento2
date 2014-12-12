<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Order\Total;

/**
 * Base class for configure totals order
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractTotal extends \Magento\Framework\Object
{
    /**
     * Process model configuration array.
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

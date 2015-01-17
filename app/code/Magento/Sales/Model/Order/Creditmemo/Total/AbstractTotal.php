<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

/**
 * Base class for credit memo total
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractTotal extends \Magento\Sales\Model\Order\Total\AbstractTotal
{
    /**
     * Collect credit memo subtotal
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        return $this;
    }
}

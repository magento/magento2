<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

/**
 * Base class for credit memo total
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class AbstractTotal extends \Magento\Sales\Model\Order\Total\AbstractTotal
{
    /**
     * Collect credit memo subtotal
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        return $this;
    }
}

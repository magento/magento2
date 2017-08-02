<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

/**
 * Class \Magento\Sales\Controller\Guest\Reorder
 *
 * @since 2.0.0
 */
class Reorder extends \Magento\Sales\Controller\AbstractController\Reorder
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Controller\Guest\OrderLoader $orderLoader
     * @param \Magento\Framework\Registry $registry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $orderLoader, $registry);
    }
}

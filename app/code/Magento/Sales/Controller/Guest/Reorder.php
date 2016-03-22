<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

class Reorder extends \Magento\Sales\Controller\AbstractController\Reorder
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Controller\Guest\OrderLoader $orderLoader
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $orderLoader, $registry);
    }
}

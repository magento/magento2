<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Store\Model\StoreManagerInterface;

class Reorder extends \Magento\Sales\Controller\AbstractController\Reorder
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Controller\Guest\OrderLoader $orderLoader
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($storeManager, $context, $orderLoader, $registry);
    }
}

<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;

class Reorder extends \Magento\Sales\Controller\AbstractController\Reorder
{
    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Guest\OrderLoader $orderLoader
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $orderLoader, $registry, $resultRedirectFactory);
    }
}

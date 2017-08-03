<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Sales\Controller\Guest\PrintAction
 *
 * @since 2.0.0
 */
class PrintAction extends \Magento\Sales\Controller\AbstractController\PrintAction
{
    /**
     * @param Context $context
     * @param OrderLoader $orderLoader
     * @param PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $orderLoader, $resultPageFactory);
    }
}

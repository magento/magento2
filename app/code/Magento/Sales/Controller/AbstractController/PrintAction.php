<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action\Context;

abstract class PrintAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @param Context $context
     * @param OrderLoaderInterface $orderLoader
     */
    public function __construct(Context $context, OrderLoaderInterface $orderLoader)
    {
        $this->orderLoader = $orderLoader;
        parent::__construct($context);
    }

    /**
     * Print Order Action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->orderLoader->load($this->_request, $this->_response)) {
            return;
        }
        $this->_view->loadLayout('print');
        $this->_view->renderLayout();
    }
}

<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action;

class View extends \Magento\Sales\Controller\AbstractController\View
{
    /**
     * @param Action\Context $context
     * @param OrderLoader $orderLoader
     */
    public function __construct(Action\Context $context, OrderLoader $orderLoader)
    {
        parent::__construct($context, $orderLoader);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->orderLoader->load($this->_request, $this->_response)) {
            return;
        }

        $this->_view->loadLayout();
        $this->_objectManager->get('Magento\Sales\Helper\Guest')->getBreadcrumbs();
        $this->_view->renderLayout();
    }
}

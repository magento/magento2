<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

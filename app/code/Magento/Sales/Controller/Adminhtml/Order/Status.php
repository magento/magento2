<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Order status management controller
 * @since 2.0.0
 */
abstract class Status extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::order_statuses';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize status model based on status code in request
     *
     * @return \Magento\Sales\Model\Order\Status|false
     * @since 2.0.0
     */
    protected function _initStatus()
    {
        $statusCode = $this->getRequest()->getParam('status');
        if ($statusCode) {
            $status = $this->_objectManager->create(\Magento\Sales\Model\Order\Status::class)->load($statusCode);
        } else {
            $status = false;
        }
        return $status;
    }
}

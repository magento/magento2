<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Grid\Massaction;

class ItemsUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->_authorization = $authorization;
    }

    /**
     * Remove massaction items in case they disallowed for user
     * @param mixed $argument
     * @return mixed
     */
    public function update($argument)
    {
        if (false === $this->_authorization->isAllowed('Magento_Sales::cancel')) {
            unset($argument['cancel_order']);
        }

        if (false === $this->_authorization->isAllowed('Magento_Sales::hold')) {
            unset($argument['hold_order']);
        }

        if (false === $this->_authorization->isAllowed('Magento_Sales::unhold')) {
            unset($argument['unhold_order']);
        }

        return $argument;
    }
}

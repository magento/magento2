<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab\Stub;

/**
 * Stub for an online payment method
 */
class OnlineMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;
}

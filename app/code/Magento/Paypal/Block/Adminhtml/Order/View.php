<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Order;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Paypal\Model\Adminhtml\Express;
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;

/**
 * Adminhtml sales order view
 * @api
 */
class View extends OrderView
{
    /**
     * @var Express
     */
    private $express;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Config $salesConfig
     * @param Reorder $reorderHelper
     * @param Express $express
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $salesConfig,
        Reorder $reorderHelper,
        Express $express,
        array $data = []
    ) {
        $this->express = $express;

        parent::__construct(
            $context,
            $registry,
            $salesConfig,
            $reorderHelper,
            $data
        );
    }

    /**
     * Constructor
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();

        $order = $this->getOrder();
        if (!$order) {
            return;
        }
        $message = __('Are you sure you want to authorize full order amount?');
        if ($this->_isAllowedAction('Magento_Paypal::authorization') && $this->canAuthorize($order)) {
            $this->addButton(
                'order_authorize',
                [
                    'label' => __('Authorize'),
                    'class' => 'authorize',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getPaymentAuthorizationUrl()}')"
                ]
            );
        }
    }

    /**
     * Returns URL for authorization of full order amount.
     *
     * @return string
     */
    private function getPaymentAuthorizationUrl(): string
    {
        return $this->getUrl('paypal/express/authorization');
    }

    /**
     * Checks if order available for payment authorization.
     *
     * @param Order $order
     * @return bool
     * @throws LocalizedException
     */
    public function canAuthorize(Order $order): bool
    {
        if ($order->canUnhold() || $order->isPaymentReview()) {
            return false;
        }

        $state = $order->getState();
        if ($order->isCanceled() || $state === Order::STATE_COMPLETE || $state === Order::STATE_CLOSED) {
            return false;
        }

        return $this->express->isOrderAuthorizationAllowed($order->getPayment());
    }
}

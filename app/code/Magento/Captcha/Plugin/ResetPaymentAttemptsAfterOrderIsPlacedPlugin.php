<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Plugin;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Reset attempts for frontend checkout
 */
class ResetPaymentAttemptsAfterOrderIsPlacedPlugin
{
    /**
     * Form ID
     */
    private const FORM_ID = 'payment_processing_request';

    /**
     * @var HelperCaptcha
     */
    private $helper;

    /**
     * @var LogFactory
     */
    private $resLogFactory;

    /**
     * ResetPaymentAttemptsAfterOrderIsPlacedPlugin constructor
     *
     * @param HelperCaptcha $helper
     * @param LogFactory $resLogFactory
     */
    public function __construct(
        HelperCaptcha $helper,
        LogFactory $resLogFactory
    ) {
        $this->helper = $helper;
        $this->resLogFactory = $resLogFactory;
    }

    /**
     * Reset attempts for frontend checkout
     *
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {
        $captchaModel = $this->helper->getCaptcha(self::FORM_ID);
        $captchaModel->setShowCaptchaInSession(false);
        $this->resLogFactory->create()->deleteUserAttempts($order->getCustomerEmail());
        return $result;
    }
}

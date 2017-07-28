<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Captcha\Observer\ResetAttemptForFrontendAccountEditObserver
 *
 * @since 2.1.0
 */
class ResetAttemptForFrontendAccountEditObserver implements ObserverInterface
{
    /**
     * Form ID
     */
    const FORM_ID = 'user_edit';

    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.1.0
     */
    protected $helper;

    /*
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     * @since 2.1.0
     */
    public $resLogFactory;

    /**
     * ResetAttemptForFrontendAccountEditObserver constructor
     *
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
    ) {
        $this->helper = $helper;
        $this->resLogFactory = $resLogFactory;
    }

    /**
     * Reset Attempts For Frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Observer\ResetAttemptForFrontendObserver
     * @since 2.1.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $email = $observer->getEmail();
        $captchaModel = $this->helper->getCaptcha(self::FORM_ID);
        $captchaModel->setShowCaptchaInSession(false);

        return $this->resLogFactory->create()->deleteUserAttempts($email);
    }
}

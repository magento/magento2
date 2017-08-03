<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;

/**
 * Class \Magento\Captcha\Observer\CheckUserLoginBackendObserver
 *
 * @since 2.0.0
 */
class CheckUserLoginBackendObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @var CaptchaStringResolver
     * @since 2.0.0
     */
    protected $captchaStringResolver;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_request;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Framework\App\RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        CaptchaStringResolver $captchaStringResolver,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_helper = $helper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_request = $request;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\Plugin\AuthenticationException
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'backend_login';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $login = $observer->getEvent()->getUsername();
        if ($captchaModel->isRequired($login)) {
            if (!$captchaModel->isCorrect($this->captchaStringResolver->resolve($this->_request, $formId))) {
                $captchaModel->logAttempt($login);
                throw new PluginAuthenticationException(__('Incorrect CAPTCHA.'));
            }
        }
        $captchaModel->logAttempt($login);

        return $this;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckRegisterCheckoutObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $_typeOnepage;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Magento\Checkout\Model\Type\Onepage $typeOnepage
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        CaptchaStringResolver $captchaStringResolver,
        \Magento\Checkout\Model\Type\Onepage $typeOnepage,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_typeOnepage = $typeOnepage;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Check Captcha On Checkout Register Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'register_during_checkout';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $checkoutMethod = $this->_typeOnepage->getQuote()->getCheckoutMethod();
        if ($checkoutMethod == \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER) {
            if ($captchaModel->isRequired()) {
                $controller = $observer->getControllerAction();
                if (
                    !$captchaModel->isCorrect($this->captchaStringResolver->resolve($controller->getRequest(), $formId))
                ) {
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $result = ['error' => 1, 'message' => __('Incorrect CAPTCHA')];
                    $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
                }
            }
        }

        return $this;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PaypalCaptcha\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ActionFlag ;

/**
 * Validates Captcha for Request Token controller
 */
class CaptchaRequestToken implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @param Data $helper
     * @param ActionFlag $actionFlag
     */
    public function __construct(Data $helper, ActionFlag $actionFlag)
    {
        $this->helper = $helper;
        $this->actionFlag = $actionFlag;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $formId = 'co-payment-form';
        $captcha = $this->helper->getCaptcha($formId);

        if (!$captcha->isRequired()) {
            return;
        }

        /** @var Action $controller */
        $controller = $observer->getControllerAction();
        $word = $controller->getRequest()->getPost('captcha_string');
        if ($captcha->isCorrect($word)) {
            return;
        }

        $data = json_encode([
            'success' => false,
            'error' => true,
            'error_messages' => __('Incorrect CAPTCHA.')
        ]);
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        $controller->getResponse()->representJson($data);
    }
}

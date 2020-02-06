<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalCaptcha\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
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
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @param Data $helper
     * @param Json $jsonSerializer
     * @param ActionFlag $actionFlag
     */
    public function __construct(Data $helper, Json $jsonSerializer, ActionFlag $actionFlag)
    {
        $this->helper = $helper;
        $this->jsonSerializer = $jsonSerializer;
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

        $data = $this->jsonSerializer->serialize([
            'success' => false,
            'error' => true,
            'error_messages' => __('Incorrect CAPTCHA.')
        ]);
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        $controller->getResponse()->representJson($data);
    }
}

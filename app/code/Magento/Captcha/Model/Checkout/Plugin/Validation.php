<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model\Checkout\Plugin;

class Validation
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $captchaHelper;

    /**
     * @var array
     */
    protected $formIds;

    /**
     * @param \Magento\Captcha\Helper\Data $captchaHelper
     * @param array $formIds
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $captchaHelper,
        array $formIds
    ) {
        $this->captchaHelper = $captchaHelper;
        $this->formIds = $formIds;
    }

    /**
     * @param \Magento\Quote\Model\AddressAdditionalDataProcessor $subject
     * @param \Magento\Quote\Api\Data\AddressAdditionalDataInterface $additionalData
     * @throws \Exception
     */
    public function beforeProcess(
        \Magento\Quote\Model\AddressAdditionalDataProcessor $subject,
        \Magento\Quote\Api\Data\AddressAdditionalDataInterface $additionalData
    ) {
        $formId = $additionalData->getExtensionAttributes()->getCaptchaFormId();
        $captchaText = $additionalData->getExtensionAttributes()->getCaptchaString();

        if ($formId !== null &&!in_array($formId, $this->formIds)) {
            throw new \Exception(__('Provided form does not exist'));
        }
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            if (!$captchaModel->isCorrect($captchaText)) {
                throw new \Exception(__('Incorrect CAPTCHA'));
            }
        }
    }
}

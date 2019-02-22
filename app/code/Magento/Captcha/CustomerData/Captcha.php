<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Helper\Data as CaptchaHelper;

/**
 * Captcha section.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Captcha implements SectionSourceInterface
{
    /**
     * @var array
     */
    private $formIds;

    /**
     * @var CaptchaHelper
     */
    private $helper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param CaptchaHelper $helper
     * @param array $formIds
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CaptchaHelper $helper,
        array $formIds,
        CustomerSession $customerSession
    ) {
        $this->helper = $helper;
        $this->formIds = $formIds;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData() :array
    {
        $data = [];

        foreach ($this->formIds as $formId) {
            /** @var DefaultModel $captchaModel */
            $captchaModel = $this->helper->getCaptcha($formId);
            $login = '';
            if ($this->customerSession->isLoggedIn()) {
                $login = $this->customerSession->getCustomerData()->getEmail();
            }
            $required =  $captchaModel->isRequired($login);
            $data[$formId] = [
                'isRequired' => $required,
                'timestamp' => time()
            ];
        }

        return $data;
    }
}

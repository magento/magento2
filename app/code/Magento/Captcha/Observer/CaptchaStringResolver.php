<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Extract given captcha word.
 */
class CaptchaStringResolver
{
    /**
     * Get Captcha String
     *
     * @param \Magento\Framework\App\RequestInterface|HttpRequest $request
     * @param string $formId
     * @return string
     */
    public function resolve(RequestInterface $request, $formId)
    {
        $captchaParams = $request->getPost(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE);
        if (!empty($captchaParams) && !empty($captchaParams[$formId])) {
            $value = $captchaParams[$formId];
        } else {
            //For Web APIs
            $value = $request->getHeader('X-Captcha');
        }

        return $value;
    }
}

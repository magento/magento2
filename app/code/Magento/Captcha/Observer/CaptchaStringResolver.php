<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Captcha\Helper\Data as CaptchaHelper;

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
        $value = '';
        $captchaParams = $request->getPost(CaptchaHelper::INPUT_NAME_FIELD_VALUE);
        if (!empty($captchaParams) && !empty($captchaParams[$formId])) {
            $value = $captchaParams[$formId];
        } elseif ($headerValue = $request->getHeader('X-Captcha')) {
            //CAPTCHA was provided via header for this XHR/web API request.
            $value = $headerValue;
        }

        return $value;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Api;

/**
 * Interface contains methods for post processing and modifies client-side CAPTCHA config
 */
interface CaptchaConfigPostProcessorInterface
{
    /**
     * Filters the data object by a filter list
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array;
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Api;

/**
 * Interface CaptchaConfigFilterInterface used by composite and leafs to implement filtering
 */
interface CaptchaConfigFilterInterface
{
    /**
     * Filters the data object by a filter list
     *
     * @param array $config
     * @return array
     */
    public function filter($config): array;
}

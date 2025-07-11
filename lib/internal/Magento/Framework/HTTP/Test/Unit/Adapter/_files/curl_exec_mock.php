<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Adapter;

use Magento\Framework\HTTP\Test\Unit\Adapter\CurlTest;

/**
 * Override global PHP function
 *
 * @SuppressWarnings("unused")
 * @param mixed $resource
 * @return string
 */
function curl_exec($resource)
{
    return CurlTest::$curlMock->exec($resource);
}

/**
 * Override global PHP function curl_setopt
 *
 * @param mixed $handle
 * @param int $option
 * @param mixed $value
 * @return bool
 * @see \curl_setopt()
 */
function curl_setopt(mixed $handle, int $option, mixed $value): bool
{
    return CurlTest::$curlMock->setopt($handle, $option, $value);
}

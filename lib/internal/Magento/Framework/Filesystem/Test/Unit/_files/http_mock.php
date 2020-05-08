<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Filesystem\Test\Unit\Driver\HttpTest;

/**
 * Override standard function
 *
 * @return string
 */
function file_get_contents()
{
    return HttpTest::$fileGetContents;
}

/**
 * Override standard function
 *
 * @return bool
 */
function file_put_contents()
{
    return HttpTest::$filePutContents;
}

/**
 * Override standard function
 *
 * @param int    $errorNumber
 * @param string $errorMessage
 * @return bool
 */
function fsockopen(&$errorNumber, &$errorMessage)
{
    $errorNumber = 0;
    $errorMessage = '';
    return HttpTest::$fsockopen;
}

/**
 * Override standard function (make a placeholder - we don't need it in our tests)
 */
function fwrite()
{
}

/**
 * Override standard function (make a placeholder - we don't need it in our tests)
 *
 * @return bool
 */
function feof()
{
    return true;
}

/**
 * Override standard function
 *
 * @return array
 */
function get_headers()
{
    return HttpTest::$headers;
}

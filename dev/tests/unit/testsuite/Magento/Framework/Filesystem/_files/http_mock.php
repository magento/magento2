<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Driver;

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

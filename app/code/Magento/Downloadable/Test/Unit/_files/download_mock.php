<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Helper;

use Magento\Downloadable\Test\Unit\Helper\DownloadTest;

/**
 * @return bool
 */
function function_exists()
{
    return DownloadTest::$functionExists;
}

/**
 * @return string
 */
function mime_content_type()
{
    return DownloadTest::$mimeContentType;
}

/**
 * Override standard function
 *
 * @return array
 */
function get_headers()
{
    return DownloadTest::$headers;
}

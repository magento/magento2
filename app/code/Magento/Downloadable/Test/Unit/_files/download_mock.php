<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Helper;

use Magento\Downloadable\Test\Unit\Helper\DownloadTest;

function function_exists()
{
    return DownloadTest::$functionExists;
}

function mime_content_type()
{
    return DownloadTest::$mimeContentType;
}

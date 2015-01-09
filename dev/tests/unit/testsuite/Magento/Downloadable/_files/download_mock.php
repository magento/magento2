<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Helper;

function function_exists()
{
    return DownloadTest::$functionExists;
}

function mime_content_type()
{
    return DownloadTest::$mimeContentType;
}

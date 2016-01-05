<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

use \Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider;

class XContentTypeOptions extends AbstractHeaderProvider
{
    protected $headerValue = 'nosniff';
    protected $headerName = 'X-Content-Type-Options';
}

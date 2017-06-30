<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider;

class XContentTypeOptions extends AbstractHeaderProvider
{
    /** @var string */
    protected $headerValue = 'nosniff';

    /** @var string */
    protected $headerName = 'X-Content-Type-Options';
}

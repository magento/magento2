<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider;

class XContentTypeOptions extends AbstractHeaderProvider
{
    /**
     * @var string
     */
    protected $headerValue = 'nosniff';

    /**
     * @var string
     */
    protected $headerName = 'X-Content-Type-Options';
}

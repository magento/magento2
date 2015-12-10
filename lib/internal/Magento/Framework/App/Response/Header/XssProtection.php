<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\Header;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HeaderProviderInterface;
use Magento\Framework\HTTP\Header;

class XssProtection implements HeaderProviderInterface
{
    /**
     * Header name
     */
    const NAME = 'X-XSS-Protection';

    /**
     * Matches IE 8 browsers
     */
    const IE_8_USER_AGENT = 'MSIE 8';

    /**
     * @var Header
     */
    private $headerService;

    /**
     * @param Header $headerService
     */
    public function __construct(Header $headerService)
    {
        $this->headerService = $headerService;
    }

    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function canApply()
    {
        return true;
    }

    /**
     * Header name
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Header value. Must be disabled for IE 8.
     *
     * @return string
     */
    public function getValue()
    {
        return strpos($this->headerService->getHttpUserAgent(), self::IE_8_USER_AGENT) === false
            ? '1; mode=block'
            : '0';
    }
}

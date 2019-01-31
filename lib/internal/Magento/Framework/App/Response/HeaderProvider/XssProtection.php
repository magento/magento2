<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\Framework\HTTP\Header;

class XssProtection extends AbstractHeaderProvider
{
    /**
     * @var string
     */
    protected $headerName = 'X-XSS-Protection';

    /** Matches IE 8 browsers */
    const IE_8_USER_AGENT = 'MSIE 8';

    /** Value for browsers except IE 8 */
    const HEADER_ENABLED = '1; mode=block';

    /** Value for IE 8 */
    const HEADER_DISABLED = '0';

    /**
     * @var \Magento\Framework\HTTP\Header
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
     * Header value. Must be disabled for IE 8.
     *
     * @return string
     */
    public function getValue()
    {
        return strpos($this->headerService->getHttpUserAgent(), self::IE_8_USER_AGENT) === false
            ? self::HEADER_ENABLED
            : self::HEADER_DISABLED;
    }
}

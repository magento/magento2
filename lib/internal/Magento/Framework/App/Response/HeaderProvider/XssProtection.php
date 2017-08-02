<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\Framework\HTTP\Header;

/**
 * Class \Magento\Framework\App\Response\HeaderProvider\XssProtection
 *
 * @since 2.1.0
 */
class XssProtection extends AbstractHeaderProvider
{
    /**
     * @var string
     * @since 2.1.0
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
     * @since 2.1.0
     */
    private $headerService;

    /**
     * @param Header $headerService
     * @since 2.1.0
     */
    public function __construct(Header $headerService)
    {
        $this->headerService = $headerService;
    }

    /**
     * Header value. Must be disabled for IE 8.
     *
     * @return string
     * @since 2.1.0
     */
    public function getValue()
    {
        return strpos($this->headerService->getHttpUserAgent(), self::IE_8_USER_AGENT) === false
            ? self::HEADER_ENABLED
            : self::HEADER_DISABLED;
    }
}

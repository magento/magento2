<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\Header;

use Magento\Framework\App\Response\HeaderProviderInterface;
use Magento\Framework\App\Response\Http;

/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 */
class XFrameOptions implements HeaderProviderInterface
{
    /** Deployment config key for frontend x-frame-options header value */
    const DEPLOYMENT_CONFIG_X_FRAME_OPT = 'x-frame-options';

    /** Always send SAMEORIGIN in backend x-frame-options header */
    const BACKEND_X_FRAME_OPT = 'SAMEORIGIN';

    /**
     * The header value
     *
     * @var string
     */
    private $xFrameOpt;

    /**
     * @param string $xFrameOpt
     */
    public function __construct($xFrameOpt)
    {
        $this->xFrameOpt = $xFrameOpt;
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
        return Http::HEADER_X_FRAME_OPT;
    }

    /**
     * Header value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->xFrameOpt;
    }
}

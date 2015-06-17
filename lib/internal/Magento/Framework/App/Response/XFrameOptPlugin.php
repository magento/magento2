<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response;

/**
 * Adds an X-FRAME-OPTIONS header to HTTP responses to safeguard against click-jacking.
 * @codeCoverageIgnore
 */
class XFrameOptPlugin
{
    /** Deployment config key for frontend x-frame-options header value */
    const DEPLOYMENT_CONFIG_X_FRAME_OPT = 'x-frame-options';

    /** Always send DENY in backend x-frame-options header */
    const BACKEND_X_FRAME_OPT = 'DENY';

    /**
     *The header value
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
     * @param \Magento\Framework\App\Response\Http $subject
     * @return void
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        $subject->setXFrameOptions($this->xFrameOpt);
    }
}

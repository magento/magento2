<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

/**
 * Page unique identifier
 */
class Identifier
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context
    ) {
        $this->request = $request;
        $this->context = $context;
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];
        return md5(serialize($data));
    }
}

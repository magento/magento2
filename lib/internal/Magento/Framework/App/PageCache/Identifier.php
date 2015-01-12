<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

/**
 * Page unique identifier
 */
class Identifier
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $data = [
            $request->isSecure(),
            $request->getRequestUri(),
            $request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING),
        ];
        $this->value = md5(serialize($data));
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}

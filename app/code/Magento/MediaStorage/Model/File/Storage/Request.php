<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\HTTP\PhpEnvironment\Request as HttpRequest;

class Request
{
    /**
     * Path info
     *
     * @var string
     */
    private $pathInfo;

    /**
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request)
    {
        $this->pathInfo = str_replace('..', '', ltrim($request->getPathInfo(), '/'));
    }

    /**
     * Retrieve path info
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }
}

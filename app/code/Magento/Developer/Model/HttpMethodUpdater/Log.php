<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Model\HttpMethodUpdater;

/**
 * HTTP method used.
 */
class Log
{
    /**
     * @var string
     */
    private $actionClass;

    /**
     * @var string
     */
    private $method;

    /**
     * @param string $actionClass
     * @param string $method
     */
    public function __construct(string $actionClass, string $method)
    {
        $this->actionClass = $actionClass;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getActionClass(): string
    {
        return $this->actionClass;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}

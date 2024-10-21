<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Helper\SecureHtmlRender;

/**
 * JS event handler data.
 *
 * @api
 */
class EventHandlerData
{
    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $event
     * @param string $code
     */
    public function __construct(string $event, string $code)
    {
        $this->event = $event;
        $this->code = $code;
    }

    /**
     * Full event name like "onclick"
     *
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * JavaScript code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}

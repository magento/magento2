<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Helper\SecureHtmlRender;

/**
 * Perform security related modifications or logic for HTML being rendered.
 *
 * @api
 */
interface SecurityProcessorInterface
{
    /**
     * Process a tag.
     *
     * @param TagData $tagData
     * @return TagData
     */
    public function processTag(TagData $tagData): TagData;

    /**
     * Process an event handler.
     *
     * @param EventHandlerData $eventHandlerData
     * @return EventHandlerData
     */
    public function processEventHandler(EventHandlerData $eventHandlerData): EventHandlerData;
}

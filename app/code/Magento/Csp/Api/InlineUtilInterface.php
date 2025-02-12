<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api;

use Magento\Csp\Api\Data\PolicyInterface;

/**
 * Utility for classes responsible for rendering and templates that allows whitelist inline sources.
 *
 * @api
 */
interface InlineUtilInterface
{
    /**
     * Render HTML tag and whitelist it as trusted source.
     *
     * Use this method to whitelist remote static resources and inline styles/scripts.
     * Do not use user-provided as any of the parameters.
     *
     * @param string $tagName
     * @param string[] $attributes
     * @param string|null $content
     * @return string
     */
    public function renderTag(string $tagName, array $attributes, ?string $content = null): string;

    /**
     * Render event listener as an HTML attribute and whitelist it as trusted source.
     *
     * Do not use user-provided values as any of the parameters.
     *
     * @param string $eventName Full attribute name like "onclick".
     * @param string $javascript
     * @return string
     */
    public function renderEventListener(string $eventName, string $javascript): string;
}

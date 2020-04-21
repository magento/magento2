<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaContentApi\Model;

/**
 * Interface for Media content Config.
 */
interface SearchPatternConfigInterface
{
    /**
     * Retrieve search RegExp patterns for finding media asset paths within content
     *
     * @return array
     */
    public function get(): array;
}

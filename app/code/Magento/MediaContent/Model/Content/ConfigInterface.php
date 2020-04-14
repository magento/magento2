<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaContent\Model\Content;

/**
 * Interface for Media content Config.
 */
interface ConfigInterface
{
    /**
     * Retrieve search regexp patterns for finding media asset paths within content
     *
     * @return array
     */
    public function getSearchPatterns() : array;
}

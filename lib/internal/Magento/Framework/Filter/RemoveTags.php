<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\FilterInterface;

/**
 * Remove tags from string
 */
class RemoveTags implements FilterInterface
{
    /**
     * Convert html entities
     *
     * @param string[] $matches
     * @return string
     */
    protected function _convertEntities($matches)
    {
        return htmlentities($matches[0]);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $value = preg_replace_callback(
            "# <(?![/a-z]) | (?<=\s)>(?![a-z]) #xi",
            [$this, '_convertEntities'],
            $value
        );
        $value = htmlspecialchars_decode($value);

        return strip_tags($value);
    }
}

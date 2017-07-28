<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Remove tags from string
 * @since 2.0.0
 */
class RemoveTags implements \Zend_Filter_Interface
{
    /**
     * Convert html entities
     *
     * @param string[] $matches
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function filter($value)
    {
        $value = preg_replace_callback(
            "# <(?![/a-z]) | (?<=\s)>(?![a-z]) #xi",
            [$this, '_convertEntities'],
            $value
        );
        $value = strip_tags($value);
        return htmlspecialchars_decode($value);
    }
}

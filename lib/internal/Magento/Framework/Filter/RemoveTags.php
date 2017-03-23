<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Remove tags from string
 */
class RemoveTags implements \Zend_Filter_Interface
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
        $value = strip_tags($value);
        return htmlspecialchars_decode($value);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Filter for standard strip_tags() function with extra functionality for html entities
 * @since 2.0.0
 */
class StripTags implements \Zend_Filter_Interface
{
    /**
     * @var \Magento\Framework\Escaper
     * @since 2.0.0
     */
    protected $escaper;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $allowableTags;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $escape;

    /**
     * @param \Magento\Framework\Escaper $escaper
     * @param null $allowableTags
     * @param bool $escape
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Escaper $escaper, $allowableTags = null, $escape = false)
    {
        $this->escaper = $escaper;
        $this->allowableTags = $allowableTags;
        $this->escape = $escape;
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
        $result = strip_tags($value, $this->allowableTags);
        return $this->escape ? $this->escaper->escapeHtml($result, $this->allowableTags) : $result;
    }
}

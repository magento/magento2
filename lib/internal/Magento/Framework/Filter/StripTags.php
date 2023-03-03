<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\FilterInterface;

/**
 * Filter for standard strip_tags() function with extra functionality for html entities
 */
class StripTags implements FilterInterface
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var string
     */
    protected $allowableTags;

    /**
     * @var bool
     */
    protected $escape;

    /**
     * @param \Magento\Framework\Escaper $escaper
     * @param string|null $allowableTags
     * @param bool $escape
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
     */
    public function filter($value)
    {
        $result = strip_tags((string)$value, $this->allowableTags);
        return $this->escape ? $this->escaper->escapeHtml($result, $this->allowableTags) : $result;
    }
}

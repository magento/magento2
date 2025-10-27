<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Form Input/Output Escape HTML entities Filter
 */
namespace Magento\Framework\Data\Form\Filter;

use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;

/**
 * EscapeHtml Form Filter Data
 */
class Escapehtml implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Escaper|null $escaper
     */
    public function __construct(
        ?Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function inputFilter($value)
    {
        return $value;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function outputFilter($value)
    {
        return $this->escaper->escapeHtml($value);
    }
}

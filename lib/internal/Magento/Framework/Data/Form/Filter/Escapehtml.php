<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form Input/Output Escape HTML entities Filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Filter;

/**
 * EscapeHtml Form Filter Data
 */
class Escapehtml implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Framework\Escaper|null $escaper
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Escaper::class
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

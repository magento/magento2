<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Source;

/**
 * A source model for verticals configuration.
 *
 * Prepares and provides options for a selector of verticals.
 */
class Verticals implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * An array of possible verticals.
     *
     * @var array
     */
    private $verticals;

    /**
     * @param array $verticals
     */
    public function __construct(array $verticals)
    {
        $this->verticals = $verticals;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => '', 'label' => __('--Please Select--')]
        ];

        foreach ($this->verticals as $vertical) {
            $result[] = ['value' => $vertical, 'label' => __($vertical)];
        }

        return $result;
    }
}

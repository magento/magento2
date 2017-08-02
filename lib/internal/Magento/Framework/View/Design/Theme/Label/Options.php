<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Label;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class \Magento\Framework\View\Design\Theme\Label\Options
 *
 * @since 2.1.0
 */
class Options implements ArrayInterface
{
    /**
     * @var ListInterface
     * @since 2.1.0
     */
    protected $list;

    /**
     * @param ListInterface $list
     * @since 2.1.0
     */
    public function __construct(ListInterface $list)
    {
        $this->list = $list;
    }

    /**
     * Return list of themes
     *
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return $this->list->getLabels();
    }
}

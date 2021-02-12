<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Label;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var ListInterface
     */
    protected $list;

    /**
     * @param ListInterface $list
     */
    public function __construct(ListInterface $list)
    {
        $this->list = $list;
    }

    /**
     * Return list of themes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->list->getLabels();
    }
}

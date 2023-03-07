<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Weee\Model\Tax;

class Display implements ArrayInterface
{
    /**
     * Retrieve list of available options to display FPT
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Tax::DISPLAY_INCL,
                'label' => __('Including FPT only')
            ],
            [
                'value' => Tax::DISPLAY_INCL_DESCR,
                'label' => __('Including FPT and FPT description')
            ],
            [
                'value' => Tax::DISPLAY_EXCL_DESCR_INCL,
                'label' => __('Excluding FPT. Including FPT description and final price')
            ],
            [
                'value' => Tax::DISPLAY_EXCL,
                'label' => __('Excluding FPT')
            ]
        ];
    }
}

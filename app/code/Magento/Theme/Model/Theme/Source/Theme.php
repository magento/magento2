<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Source model for eav attribute custom_design
 */
namespace Magento\Theme\Model\Theme\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\View\Design\Theme\Label;

/**
 * Design
 *
 */
class Theme extends AbstractSource
{
    /**
     * @param Label $themeLabel
     */
    public function __construct(
        protected readonly Label $themeLabel
    ) {
    }

    /**
     * Retrieve All Design Theme Options
     *
     * @param bool $withEmpty add empty (please select) values to result
     * @return Label[]
     */
    public function getAllOptions($withEmpty = true)
    {
        $label = $withEmpty ? __('-- Please Select --') : $withEmpty;
        return $this->_options = $this->themeLabel->getLabelsCollection($label);
    }
}

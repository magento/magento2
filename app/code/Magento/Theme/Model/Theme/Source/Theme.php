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
 * @since 2.0.0
 */
class Theme extends AbstractSource
{
    /**
     * @var Label
     * @since 2.0.0
     */
    protected $themeLabel;

    /**
     * @param Label $themeLabel
     * @since 2.0.0
     */
    public function __construct(Label $themeLabel)
    {
        $this->themeLabel = $themeLabel;
    }

    /**
     * Retrieve All Design Theme Options
     *
     * @param bool $withEmpty add empty (please select) values to result
     * @return Label[]
     * @since 2.0.0
     */
    public function getAllOptions($withEmpty = true)
    {
        $label = $withEmpty ? __('-- Please Select --') : $withEmpty;
        return $this->_options = $this->themeLabel->getLabelsCollection($label);
    }
}

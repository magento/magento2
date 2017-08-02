<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Design\Theme\Label\ListInterface;

/**
 * Class Theme
 * @since 2.0.0
 */
class Theme implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Label\ListInterface
     * @since 2.0.0
     */
    protected $themeList;

    /**
     * Constructor
     *
     * @param ListInterface $themeList
     * @since 2.0.0
     */
    public function __construct(ListInterface $themeList)
    {
        $this->themeList = $themeList;
    }

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $options[] = ['label' => 'Default', 'value' => ''];
        return array_merge($options, $this->themeList->getLabels());
    }
}

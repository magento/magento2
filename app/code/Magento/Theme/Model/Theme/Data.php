<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Model\Theme;

/**
 * Data model for themes
 *
 * @method \Magento\Framework\View\Design\ThemeInterface setArea(string $area)
 */
class Data extends \Magento\Theme\Model\Theme
{
    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->getData('area');
    }
}

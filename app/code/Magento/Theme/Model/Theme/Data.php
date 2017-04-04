<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

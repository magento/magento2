<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Theme;

/**
 * Data model for themes
 *
 * @method \Magento\Framework\View\Design\ThemeInterface setArea(string $area)
 */
class Data extends \Magento\Core\Model\Theme
{
    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->getData('area');
    }
}

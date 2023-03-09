<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme;

/**
 * Data model for themes
 *
 * @method ThemeInterface setArea(string $area)
 */
class Data extends Theme
{
    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->getData('area');
    }
}

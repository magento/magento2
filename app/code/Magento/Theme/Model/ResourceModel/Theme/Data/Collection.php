<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Data;

use Magento\Framework\View\Design\Theme\Label\ListInterface as ThemeLabelListInterface;
use Magento\Framework\View\Design\Theme\ListInterface as ThemeListInterface;
use Magento\Theme\Model\ResourceModel\Theme as ResourceTheme;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\Theme\Data as ModelThemeData;

/**
 * Theme data collection
 */
class Collection extends ThemeCollection implements ThemeLabelListInterface, ThemeListInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ModelThemeData::class, ResourceTheme::class);
    }
}

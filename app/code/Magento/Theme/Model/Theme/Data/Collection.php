<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme\Data;

use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Theme\Model\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\Theme\Data as ThemeData;

/**
 * Theme filesystem data collection
 */
class Collection extends ThemeCollection implements ListInterface
{
    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = ThemeData::class;
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Model\Theme\Data;

use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * Theme filesystem data collection
 */
class Collection extends \Magento\Theme\Model\Theme\Collection implements ListInterface
{
    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = \Magento\Theme\Model\Theme\Data::class;
}

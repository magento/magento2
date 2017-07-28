<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme\Data;

use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * Theme filesystem data collection
 * @since 2.0.0
 */
class Collection extends \Magento\Theme\Model\Theme\Collection implements ListInterface
{
    /**
     * Model of collection item
     *
     * @var string
     * @since 2.0.0
     */
    protected $_itemObjectClass = \Magento\Theme\Model\Theme\Data::class;
}

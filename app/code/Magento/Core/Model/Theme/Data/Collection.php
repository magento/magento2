<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Theme\Data;

use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * Theme filesystem data collection
 */
class Collection extends \Magento\Core\Model\Theme\Collection implements ListInterface
{
    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = 'Magento\Core\Model\Theme\Data';
}

<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

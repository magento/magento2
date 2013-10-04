<?php
/**
 * Hash Optimized option array
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Resource\Rule\Grid\Options;

class HashOptimized
    implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * @var \Magento\Tax\Model\Resource\Calculation\Rate\Collection
     */
    protected $_collection;

    /**
     * @param \Magento\Tax\Model\Resource\Calculation\Rate\Collection $collection
     */
    public function __construct(\Magento\Tax\Model\Resource\Calculation\Rate\Collection $collection)
    {
        $this->_collection = $collection;
    }

    /**
     * Return Hash Optimized array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_collection->toOptionHashOptimized();
    }
}

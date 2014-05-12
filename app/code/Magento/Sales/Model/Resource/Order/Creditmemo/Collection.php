<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Order\Creditmemo;

/**
 * Flat sales order creditmemo collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\Resource\Order\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_creditmemo_collection';

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'order_id';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Creditmemo', 'Magento\Sales\Model\Resource\Order\Creditmemo');
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
        return $this;
    }

    /**
     * Add filtration conditions
     *
     * @param array|null $filter
     * @return $this
     */
    public function getFiltered($filter = null)
    {
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                $this->addFieldToFilter($field, $value);
            }
        }
        return $this;
    }
}

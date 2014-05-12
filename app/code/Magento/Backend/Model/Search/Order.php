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
namespace Magento\Backend\Model\Search;

/**
 * Search Order Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Order extends \Magento\Framework\Object
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     */
    public function __construct(
        \Magento\Sales\Model\Resource\Order\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $adminhtmlData
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_adminhtmlData = $adminhtmlData;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $query = $this->getQuery();
        //TODO: add full name logic
        $collection = $this->_collectionFactory->create()->addAttributeToSelect(
            '*'
        )->addAttributeToSearchFilter(
            array(
                array('attribute' => 'increment_id', 'like' => $query . '%'),
                array('attribute' => 'billing_firstname', 'like' => $query . '%'),
                array('attribute' => 'billing_lastname', 'like' => $query . '%'),
                array('attribute' => 'billing_telephone', 'like' => $query . '%'),
                array('attribute' => 'billing_postcode', 'like' => $query . '%'),
                array('attribute' => 'shipping_firstname', 'like' => $query . '%'),
                array('attribute' => 'shipping_lastname', 'like' => $query . '%'),
                array('attribute' => 'shipping_telephone', 'like' => $query . '%'),
                array('attribute' => 'shipping_postcode', 'like' => $query . '%')
            )
        )->setCurPage(
            $this->getStart()
        )->setPageSize(
            $this->getLimit()
        )->load();

        foreach ($collection as $order) {
            $result[] = array(
                'id' => 'order/1/' . $order->getId(),
                'type' => __('Order'),
                'name' => __('Order #%1', $order->getIncrementId()),
                'description' => $order->getBillingFirstname() . ' ' . $order->getBillingLastname(),
                'form_panel_title' => __(
                    'Order #%1 (%2)',
                    $order->getIncrementId(),
                    $order->getBillingFirstname() . ' ' . $order->getBillingLastname()
                ),
                'url' => $this->_adminhtmlData->getUrl('sales/order/view', array('order_id' => $order->getId()))
            );
        }

        $this->setResults($result);

        return $this;
    }
}

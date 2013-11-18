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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales order view block
 */
namespace Magento\Sales\Block\Reorder;

class Sidebar extends \Magento\Core\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/history.phtml';

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * Store list manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Init orders
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->_customerSession->isLoggedIn()) {
            $this->initOrders();
        }
    }

    /**
     * Init customer order for display on front
     */
    public function initOrders()
    {
        $customerId = $this->getCustomerId() ? $this->getCustomerId()
            : $this->_customerSession->getCustomer()->getId();

        $orders = $this->_orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId)
            ->addAttributeToFilter('state',
                array('in' => $this->_orderConfig->getVisibleOnFrontStates())
            )
            ->addAttributeToSort('created_at', 'desc')
            ->setPage(1, 1);
        //TODO: add filter by current website

        $this->setOrders($orders);
    }

    /**
     * Get list of last ordered products
     *
     * @return array
     */
    public function getItems()
    {
        $items = array();
        $order = $this->getLastOrder();
        $limit = 5;

        if ($order) {
            $website = $this->_storeManager->getStore()->getWebsiteId();
            foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                if ($item->getProduct() && in_array($website, $item->getProduct()->getWebsiteIds())) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Check item product availability for reorder
     *
     * @param  \Magento\Sales\Model\Order\Item $orderItem
     * @return boolean
     */
    public function isItemAvailableForReorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        if ($orderItem->getProduct()) {
            return $orderItem->getProduct()->getStockItem()->getIsInStock();
        }
        return false;
    }

    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('checkout/cart/addgroup', array('_secure' => true));
    }

    /**
     * Last order getter
     *
     * @return \Magento\Sales\Model\Order|false
     */
    public function getLastOrder()
    {
        foreach ($this->getOrders() as $order) {
            return $order;
        }
        return false;
    }

    /**
     * Render "My Orders" sidebar block
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_customerSession->isLoggedIn() || $this->getCustomerId() ? parent::_toHtml() : '';
    }
}

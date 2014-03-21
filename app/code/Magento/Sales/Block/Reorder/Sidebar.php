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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Reorder;

/**
 * Sales order view block
 */
class Sidebar extends \Magento\View\Element\Template implements \Magento\View\Block\IdentityInterface
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
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\App\Http\Context $httpContext,
        array $data = array()
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Init orders
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH)) {
            $this->initOrders();
        }
    }

    /**
     * Init customer order for display on front
     *
     * @return void
     */
    public function initOrders()
    {
        $customerId = $this->getCustomerId() ? $this
            ->getCustomerId() : $this
            ->_customerSession
            ->getCustomer()
            ->getId();

        $orders = $this->_orderCollectionFactory->create()->addAttributeToFilter(
            'customer_id',
            $customerId
        )->addAttributeToFilter(
            'state',
            array('in' => $this->_orderConfig->getVisibleOnFrontStates())
        )->addAttributeToSort(
            'created_at',
            'desc'
        )->setPage(
            1,
            1
        );
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
        if ($this->getOrders()) {
            foreach ($this->getOrders() as $order) {
                return $order;
            }
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
        $isValid = $this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH) || $this->getCustomerId();
        return $isValid ? parent::_toHtml() : '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = array();
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getProduct()->getIdentities());
        }
        return $identities;
    }
}

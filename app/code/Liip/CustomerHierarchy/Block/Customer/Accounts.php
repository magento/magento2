<?php

namespace Liip\CustomerHierarchy\Block\Customer;

class Accounts extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customers;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getAccounts()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        if (null === $this->customers) {
            $this->customers = $this->customerCollectionFactory->create();
        }

        return $this->customers;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAccounts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customerhierarchy.customer.accounts.pager'
            )->setCollection(
                $this->getAccounts()
            );
            $this->setChild('pager', $pager);
            $this->getAccounts()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return 'http://example.com';//$this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getDisableUrl($order)
    {
        return 'http://example.com';//$this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getEditUrl($order)
    {
        return 'http://example.com';//$this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getDeleteUrl($order)
    {
        return 'http://example.com';//$this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}

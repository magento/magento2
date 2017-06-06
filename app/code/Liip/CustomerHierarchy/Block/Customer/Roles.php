<?php

namespace Liip\CustomerHierarchy\Block\Customer;

class Roles extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $roles;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $roleCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Liip\CustomerHierarchy\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getRoles()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        if (null === $this->roles) {
            $this->roles = $this->roleCollectionFactory->create();
        }

        return $this->roles;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRoles()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customerhierarchy.customer.roles.pager'
            )->setCollection(
                $this->getRoles()
            );
            $this->setChild('pager', $pager);
            $this->getRoles()->load();
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

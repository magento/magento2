<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model;

/**
 * Login model
 */
class Login extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Login tome frame
     */
    const TIME_FRAME = 60;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magefan_login_as_customer';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'loginascustomer_login';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_random;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_random = $random;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magefan\LoginAsCustomer\Model\ResourceModel\Login');
    }

    /**
     * Retrieve not used admin login
     * @param  string $secret
     * @return self
     */
    public function loadNotUsed($secret)
    {
        return $this->getCollection()
            ->addFieldToFilter('secret', $secret)
            ->addFieldToFilter('used', 0)
            ->addFieldToFilter('created_at', ['gt' => $this->getDateTimePoint()])
            ->setPageSize(1)
            ->getFirstItem();
    }

    /**
     * Delete not used credentials
     * @return void
     */
    public function deleteNotUsed()
    {
        $resource = $this->getResource();
        $resource->getConnection()->delete(
            $resource->getTable('magefan_login_as_customer'), [
                'created_at < ?' => $this->getDateTimePoint(),
                'used = ?' => 0,
            ]
        );
    }

    /**
     * Retrieve login datetime point
     * @return [type] [description]
     */
    protected function getDateTimePoint()
    {
        return date('Y-m-d H:i:s', $this->_dateTime->gmtTimestamp() - self::TIME_FRAME);
    }

    /**
     * Retrieve customer
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = $this->_customerFactory->create()
                ->load($this->getCustomerId());
        }
        return $this->_customer;
    }

    /**
     * Login Customer
     * @return false || \Magento\Customer\Model\Customer
     */
    public function authenticateCustomer()
    {
        $customer = $this->getCustomer();

        if (!$customer->getId()) {
            throw new \Exception(__("Customer are no longer exist."), 1);
        }

        if ($this->_customerSession->loginById($customer->getId())) {
            $this->_customerSession->regenerateId();
            $this->_customerSession->setLoggedAsCustomerAdmindId(
                $this->getAdminId()
            );
        }

        $this->setUsed(1)->save();

        return $customer;
    }

    /**
     * Generate new login credentials
     * @param  int $adminId
     * @return $this
     */
    public function generate($adminId)
    {
        return $this->setData([
            'customer_id' => $this->getCustomerId(),
            'admin_id' => $adminId,
            'secret' => $this->_random->getRandomString(64),
            'used' => 0,
            'created_at' => $this->_dateTime->gmtTimestamp(),
        ])->save();
    }

}

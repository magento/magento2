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
namespace Magento\Customer\Block\Account\Dashboard;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Dashboard Customer Info
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Cached subscription object
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscription;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /** @var \Magento\Customer\Helper\View */
    protected $_helperView;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
        array $data = array()
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_helperView = $helperView;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Returns the Magento Customer Model for this block
     *
     * @return \Magento\Customer\Service\V1\Data\Customer|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get the full name of a customer
     *
     * @return string full name
     */
    public function getName()
    {
        return $this->_helperView->getCustomerName($this->getCustomer());
    }

    /**
     * @return string
     */
    public function getChangePasswordUrl()
    {
        return $this->_urlBuilder->getUrl('*/account/edit/changepass/1');
    }

    /**
     * Get Customer Subscription Object Information
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriptionObject()
    {
        if (!$this->_subscription) {
            $this->_subscription = $this->_createSubscriber();
            $customer = $this->getCustomer();
            if ($customer) {
                $this->_subscription->loadByEmail($customer->getEmail());
            }
        }
        return $this->_subscription;
    }

    /**
     * Gets Customer subscription status
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->getLayout()->getBlockSingleton('Magento\Customer\Block\Form\Register')->isNewsletterEnabled();
    }

    /**
     * @return \Magento\Newsletter\Model\Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }
}

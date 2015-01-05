<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Persistent\Model\Observer;

class DisableGuestCheckout
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     */
    public function __construct(\Magento\Persistent\Helper\Session $persistentSession)
    {
        $this->_persistentSession = $persistentSession;
    }

    /**
     * Disable guest checkout if we are in persistent mode
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute($observer)
    {
        if ($this->_persistentSession->isPersistent()) {
            $observer->getEvent()->getResult()->setIsAllowed(false);
        }
    }
}

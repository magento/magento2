<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sendfriend Observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sendfriend\Model;

class Observer
{
    /**
     * @var \Magento\Sendfriend\Model\SendfriendFactory
     */
    protected $_sendfriendFactory;

    /**
     * @param \Magento\Sendfriend\Model\SendfriendFactory $sendfriendFactory
     */
    public function __construct(\Magento\Sendfriend\Model\SendfriendFactory $sendfriendFactory)
    {
        $this->_sendfriendFactory = $sendfriendFactory;
    }

    /**
     * Register Sendfriend Model in global registry
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function register(\Magento\Framework\Event\Observer $observer)
    {
        $this->_sendfriendFactory->create()->register();
        return $this;
    }
}

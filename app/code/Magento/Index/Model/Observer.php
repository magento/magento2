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
 * @package     Magento_Index
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Index\Model;

class Observer
{
    /**
     * @var \Magento\Index\Model\Indexer
     *
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexer;

    /**
     * @param \Magento\Index\Model\Indexer $indexer
     */
    public function __construct(
        \Magento\Index\Model\Indexer $indexer
    ) {
        $this->_indexer = $indexer;
    }

    /**
     * Store after commit observer. Process store related indexes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processStoreSave(\Magento\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $this->_indexer->processEntityAction(
            $store,
            \Magento\Core\Model\Store::ENTITY,
            \Magento\Index\Model\Event::TYPE_SAVE
        );
    }

    /**
     * Store group after commit observer. Process store group related indexes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processStoreGroupSave(\Magento\Event\Observer $observer)
    {
        $storeGroup = $observer->getEvent()->getStoreGroup();
        $this->_indexer->processEntityAction(
            $storeGroup,
            \Magento\Core\Model\Store\Group::ENTITY,
            \Magento\Index\Model\Event::TYPE_SAVE
        );
    }

    /**
     * Website save after commit observer. Process website related indexes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processWebsiteSave(\Magento\Event\Observer $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $this->_indexer->processEntityAction(
            $website,
            \Magento\Core\Model\Website::ENTITY,
            \Magento\Index\Model\Event::TYPE_SAVE
        );
    }

    /**
     * Store after commit observer. Process store related indexes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processStoreDelete(\Magento\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $this->_indexer->processEntityAction(
            $store,
            \Magento\Core\Model\Store::ENTITY,
            \Magento\Index\Model\Event::TYPE_DELETE
        );
    }

    /**
     * Store group after commit observer. Process store group related indexes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processStoreGroupDelete(\Magento\Event\Observer $observer)
    {
        $storeGroup = $observer->getEvent()->getStoreGroup();
        $this->_indexer->processEntityAction(
            $storeGroup,
            \Magento\Core\Model\Store\Group::ENTITY,
            \Magento\Index\Model\Event::TYPE_DELETE
        );
    }

    /**
     * Website save after commit observer. Process website related indexes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processWebsiteDelete(\Magento\Event\Observer $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $this->_indexer->processEntityAction(
            $website,
            \Magento\Core\Model\Website::ENTITY,
            \Magento\Index\Model\Event::TYPE_DELETE
        );
    }

    /**
     * Config data after commit observer.
     *
     * @param \Magento\Event\Observer $observer
     */
    public function processConfigDataSave(\Magento\Event\Observer $observer)
    {
        $configData = $observer->getEvent()->getConfigData();
        $this->_indexer->processEntityAction(
            $configData,
            \Magento\Core\Model\Config\Value::ENTITY,
            \Magento\Index\Model\Event::TYPE_SAVE
        );
    }
}

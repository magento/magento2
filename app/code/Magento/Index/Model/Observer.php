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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Index\Model;

use Magento\Event\Observer as EventObserver;

class Observer
{
    /**
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexer;

    /**
     * @param \Magento\Index\Model\Indexer $indexer
     */
    public function __construct(\Magento\Index\Model\Indexer $indexer)
    {
        $this->_indexer = $indexer;
    }

    /**
     * Store after commit observer. Process store related indexes
     *
     * @param EventObserver $observer
     * @return void
     */
    public function processStoreSave(EventObserver $observer)
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
     * @param EventObserver $observer
     * @return void
     */
    public function processStoreGroupSave(EventObserver $observer)
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
     * @param EventObserver $observer
     * @return void
     */
    public function processWebsiteSave(EventObserver $observer)
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
     * @param EventObserver $observer
     * @return void
     */
    public function processStoreDelete(EventObserver $observer)
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
     * @param EventObserver $observer
     * @return void
     */
    public function processStoreGroupDelete(EventObserver $observer)
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
     * @param EventObserver $observer
     * @return void
     */
    public function processWebsiteDelete(EventObserver $observer)
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
     * @param EventObserver $observer
     * @return void
     */
    public function processConfigDataSave(EventObserver $observer)
    {
        $configData = $observer->getEvent()->getConfigData();
        $this->_indexer->processEntityAction(
            $configData,
            \Magento\App\Config\ValueInterface::ENTITY,
            \Magento\Index\Model\Event::TYPE_SAVE
        );
    }
}

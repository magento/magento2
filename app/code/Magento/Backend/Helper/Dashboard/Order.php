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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Helper\Dashboard;

/**
 * Adminhtml dashboard helper for orders
 */
class Order extends \Magento\Backend\Helper\Dashboard\AbstractDashboard
{
    /**
     * @var \Magento\Reports\Model\Resource\Order\Collection
     */
    protected $_orderCollection;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Locale $locale
     * @param \Magento\App\State $appState
     * @param \Magento\Reports\Model\Resource\Order\Collection $orderCollection
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Locale $locale,
        \Magento\App\State $appState,
        \Magento\Reports\Model\Resource\Order\Collection $orderCollection,
        $dbCompatibleMode = true
    ) {
        $this->_orderCollection = $orderCollection;
        parent::__construct(
            $context,
            $coreStoreConfig,
            $storeManager,
            $locale,
            $appState,
            $dbCompatibleMode
        );
    }

    /**
     * @return void
     */
    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = $this->_orderCollection->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } else if ($this->getParam('website')){
            $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } else if ($this->getParam('group')){
            $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter('store_id',
                array('eq' => $this->_storeManager->getStore(\Magento\Core\Model\Store::ADMIN_CODE)->getId())
            );
        }



        $this->_collection->load();
    }

}

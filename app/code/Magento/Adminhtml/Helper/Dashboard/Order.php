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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard helper for orders
 */
namespace Magento\Adminhtml\Helper\Dashboard;

class Order extends \Magento\Adminhtml\Helper\Dashboard\AbstractDashboard
{
    /**
     * @var \Magento\Reports\Model\Resource\Order\Collection
     */
    protected $_orderCollection;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Http $coreHttp
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Locale $locale
     * @param \Magento\Core\Model\Date $dateModel
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\Encryption $encryptor
     * @param \Magento\Reports\Model\Resource\Order\Collection $orderCollection
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Http $coreHttp,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Locale $locale,
        \Magento\Core\Model\Date $dateModel,
        \Magento\App\State $appState,
        \Magento\Core\Model\Encryption $encryptor,
        \Magento\Reports\Model\Resource\Order\Collection $orderCollection,
        $dbCompatibleMode = true
    )
    {
        $this->_orderCollection = $orderCollection;
        parent::__construct($context, $eventManager, $coreHttp, $config, $coreStoreConfig, $storeManager,
            $locale, $dateModel, $appState, $encryptor, $dbCompatibleMode
        );
    }

    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = $this->_orderCollection->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } else if ($this->getParam('website')){
            $storeIds = $this->_storeManger->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } else if ($this->getParam('group')){
            $storeIds = $this->_storeManger->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter('store_id',
                array('eq' => $this->_storeManger->getStore(\Magento\Core\Model\Store::ADMIN_CODE)->getId())
            );
        }



        $this->_collection->load();
    }

}

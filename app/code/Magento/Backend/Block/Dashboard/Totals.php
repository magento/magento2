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


/**
 * Adminhtml dashboard totals bar
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Dashboard;

use Magento\Backend\Block\Widget;

class Totals extends \Magento\Backend\Block\Dashboard\Bar
{
    /**
     * @var string
     */
    protected $_template = 'dashboard/totalbar.phtml';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\Resource\Order\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\Resource\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = array()
    ) {
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $collectionFactory, $data);
    }

    /**
     * @return $this|void
     */
    protected function _prepareLayout()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam(
            'store'
        ) || $this->getRequest()->getParam(
            'website'
        ) || $this->getRequest()->getParam(
            'group'
        );
        $period = $this->getRequest()->getParam('period', '24h');

        /* @var $collection \Magento\Reports\Model\Resource\Order\Collection */
        $collection = $this->_collectionFactory->create()->addCreateAtPeriodFilter(
            $period
        )->calculateTotals(
            $isFilter
        );

        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
        } else {
            if ($this->getRequest()->getParam('website')) {
                $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
                $collection->addFieldToFilter('store_id', array('in' => $storeIds));
            } else {
                if ($this->getRequest()->getParam('group')) {
                    $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
                    $collection->addFieldToFilter('store_id', array('in' => $storeIds));
                } elseif (!$collection->isLive()) {
                    $collection->addFieldToFilter(
                        'store_id',
                        array('eq' => $this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId())
                    );
                }
            }
        }

        $collection->load();

        $totals = $collection->getFirstItem();

        $this->addTotal(__('Revenue'), $totals->getRevenue());
        $this->addTotal(__('Tax'), $totals->getTax());
        $this->addTotal(__('Shipping'), $totals->getShipping());
        $this->addTotal(__('Quantity'), $totals->getQuantity() * 1, true);
    }
}

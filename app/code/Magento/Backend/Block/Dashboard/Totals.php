<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Dashboard;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\ResourceModel\Order\Collection;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Framework\App\ObjectManager;

/**
 * Adminhtml dashboard totals bar
 * @api
 * @since 100.0.2
 */
class Totals extends Bar
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/totalbar.phtml';

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var Period
     */
    private $period;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Manager $moduleManager
     * @param array $data
     * @param Period|null $period
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Manager $moduleManager,
        array $data = [],
        ?Period $period = null
    ) {
        $this->_moduleManager = $moduleManager;
        $this->period = $period ?? ObjectManager::getInstance()->get(Period::class);
        parent::__construct($context, $collectionFactory, $data);
    }

    /**
     * @inheritDoc
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
        $firstPeriod = array_key_first($this->period->getDatePeriods());
        $period = $this->getRequest()->getParam('period', $firstPeriod);

        /* @var $collection Collection */
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
                $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
            } else {
                if ($this->getRequest()->getParam('group')) {
                    $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
                    $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
                } elseif (!$collection->isLive()) {
                    $collection->addFieldToFilter(
                        'store_id',
                        ['eq' => $this->_storeManager->getStore(Store::ADMIN_CODE)->getId()]
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

        return $this;
    }
}

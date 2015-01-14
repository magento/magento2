<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 *  Refresh Statistic Grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Refresh;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Reports\Model\FlagFactory
     */
    protected $_reportsFlagFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory
    ) {
        parent::__construct($entityFactory);
        $this->_localeDate = $localeDate;
        $this->_reportsFlagFactory = $reportsFlagFactory;
    }

    /**
     * Get if updated
     *
     * @param string $reportCode
     * @return string|\Magento\Framework\Stdlib\DateTime\DateInterface
     */
    protected function _getUpdatedAt($reportCode)
    {
        $flag = $this->_reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $this->_localeDate->scopeDate(
            0,
            new \Magento\Framework\Stdlib\DateTime\Date(
                $flag->getLastUpdate(),
                \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
            ),
            true
        ) : '';
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!count($this->_items)) {
            $data = [
                [
                    'id' => 'sales',
                    'report' => __('Orders'),
                    'comment' => __('Total Ordered Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_ORDER_FLAG_CODE),
                ],
                [
                    'id' => 'tax',
                    'report' => __('Tax'),
                    'comment' => __('Order Taxes Report Grouped by Tax Rates'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_TAX_FLAG_CODE)
                ],
                [
                    'id' => 'shipping',
                    'report' => __('Shipping'),
                    'comment' => __('Total Shipped Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_SHIPPING_FLAG_CODE)
                ],
                [
                    'id' => 'invoiced',
                    'report' => __('Total Invoiced'),
                    'comment' => __('Total Invoiced VS Paid Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_INVOICE_FLAG_CODE)
                ],
                [
                    'id' => 'refunded',
                    'report' => __('Total Refunded'),
                    'comment' => __('Total Refunded Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_REFUNDED_FLAG_CODE)
                ],
                [
                    'id' => 'coupons',
                    'report' => __('Coupons'),
                    'comment' => __('Promotion Coupons Usage Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_COUPONS_FLAG_CODE)
                ],
                [
                    'id' => 'bestsellers',
                    'report' => __('Bestsellers'),
                    'comment' => __('Products Bestsellers Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_BESTSELLERS_FLAG_CODE)
                ],
                [
                    'id' => 'viewed',
                    'report' => __('Most Viewed'),
                    'comment' => __('Most Viewed Products Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magento\Reports\Model\Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE)
                ],
            ];
            foreach ($data as $value) {
                $item = new \Magento\Framework\Object();
                $item->setData($value);
                $this->addItem($item);
            }
        }
        return $this;
    }
}

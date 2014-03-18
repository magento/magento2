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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Report;

/**
 * Order entity resource model
 */
class Order extends AbstractReport
{
    /**
     * @var \Magento\Sales\Model\Resource\Report\Order\CreatedatFactory
     */
    protected $_createDatFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Report\Order\UpdatedatFactory
     */
    protected $_updateDatFactory;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Logger $logger
     * @param \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Sales\Model\Resource\Report\Order\CreatedatFactory $createDatFactory
     * @param \Magento\Sales\Model\Resource\Report\Order\UpdatedatFactory $updateDatFactory
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Logger $logger,
        \Magento\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Sales\Model\Resource\Report\Order\CreatedatFactory $createDatFactory,
        \Magento\Sales\Model\Resource\Report\Order\UpdatedatFactory $updateDatFactory
    ) {
        parent::__construct($resource, $logger, $localeDate, $reportsFlagFactory, $dateTime, $timezoneValidator);
        $this->_createDatFactory = $createDatFactory;
        $this->_updateDatFactory = $updateDatFactory;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_aggregated_created', 'id');
    }

    /**
     * Aggregate Orders data
     *
     * @param string|int|\Zend_Date|array|null $from
     * @param string|int|\Zend_Date|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_createDatFactory->create()->aggregate($from, $to);
        $this->_updateDatFactory->create()->aggregate($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_ORDER_FLAG_CODE);
        return $this;
    }
}

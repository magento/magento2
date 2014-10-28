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
namespace Magento\SalesRule\Model\Resource\Report;

/**
 * Rule report resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rule extends \Magento\Reports\Model\Resource\Report\AbstractReport
{
    /**
     * @var \Magento\SalesRule\Model\Resource\Report\Rule\CreatedatFactory
     */
    protected $_createdatFactory;

    /**
     * @var \Magento\SalesRule\Model\Resource\Report\Rule\UpdatedatFactory
     */
    protected $_updatedatFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\SalesRule\Model\Resource\Report\Rule\CreatedatFactory $createdatFactory
     * @param \Magento\SalesRule\Model\Resource\Report\Rule\UpdatedatFactory $updatedatFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\SalesRule\Model\Resource\Report\Rule\CreatedatFactory $createdatFactory,
        \Magento\SalesRule\Model\Resource\Report\Rule\UpdatedatFactory $updatedatFactory
    ) {
        parent::__construct($resource, $logger, $localeDate, $reportsFlagFactory, $dateTime, $timezoneValidator);
        $this->_createdatFactory = $createdatFactory;
        $this->_updatedatFactory = $updatedatFactory;
    }

    /**
     * Resource Report Rule constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setResource('salesrule');
    }

    /**
     * Aggregate Coupons data
     *
     * @param mixed|null $from
     * @param mixed|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_createdatFactory->create()->aggregate($from, $to);
        $this->_updatedatFactory->create()->aggregate($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_COUPONS_FLAG_CODE);

        return $this;
    }

    /**
     * Get all unique Rule Names from aggregated coupons usage data
     *
     * @return array
     */
    public function getUniqRulesNamesList()
    {
        $adapter = $this->_getReadAdapter();
        $tableName = $this->getTable('coupon_aggregated');
        $select = $adapter->select()->from(
            $tableName,
            new \Zend_Db_Expr('DISTINCT rule_name')
        )->where(
            'rule_name IS NOT NULL'
        )->where(
            'rule_name <> ?',
            ''
        )->order(
            'rule_name ASC'
        );

        $rulesNames = $adapter->fetchAll($select);

        $result = array();

        foreach ($rulesNames as $row) {
            $result[] = $row['rule_name'];
        }

        return $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report;

use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\Flag;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Report\AbstractReport;
use Magento\SalesRule\Model\ResourceModel\Report\Rule\CreatedatFactory;
use Magento\SalesRule\Model\ResourceModel\Report\Rule\UpdatedatFactory;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * Rule report resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rule extends AbstractReport
{
    /**
     * @var CreatedatFactory
     */
    protected $_createdatFactory;

    /**
     * @var UpdatedatFactory
     */
    protected $_updatedatFactory;

    /**
     * @param DbContext $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param CreatedatFactory $createdatFactory
     * @param UpdatedatFactory $updatedatFactory
     * @param string $connectionName
     */
    public function __construct(
        DbContext $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        CreatedatFactory $createdatFactory,
        UpdatedatFactory $updatedatFactory,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
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
        $this->_setFlagData(Flag::REPORT_COUPONS_FLAG_CODE);

        return $this;
    }

    /**
     * Get all unique Rule Names from aggregated coupons usage data
     *
     * @return array
     */
    public function getUniqRulesNamesList()
    {
        $resourceModel = $this->_createdatFactory->create();
        $connection = $resourceModel->getConnection();
        $tableName = $resourceModel->getMainTable();
        $select = $connection->select()->from(
            $tableName,
            new Zend_Db_Expr('DISTINCT rule_name')
        )->where(
            'rule_name IS NOT NULL'
        )->where(
            'rule_name <> ?',
            ''
        )->order(
            'rule_name ASC'
        );

        $rulesNames = $connection->fetchAll($select);

        $result = [];

        foreach ($rulesNames as $row) {
            $result[] = $row['rule_name'];
        }

        return $result;
    }
}

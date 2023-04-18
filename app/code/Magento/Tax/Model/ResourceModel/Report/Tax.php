<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax report resource model
 */
namespace Magento\Tax\Model\ResourceModel\Report;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime as FrameworkDateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\Flag;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Report\AbstractReport;
use Magento\Tax\Model\ResourceModel\Report\Tax\Createdat;
use Magento\Tax\Model\ResourceModel\Report\Tax\CreatedatFactory;
use Magento\Tax\Model\ResourceModel\Report\Tax\Updatedat;
use Magento\Tax\Model\ResourceModel\Report\Tax\UpdatedatFactory;
use Psr\Log\LoggerInterface;

class Tax extends AbstractReport
{
    /**
     * @var CreatedatFactory
     */
    protected $_createdAtFactory;

    /**
     * @var UpdatedatFactory
     */
    protected $_updatedAtFactory;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param FrameworkDateTime $dateTime
     * @param CreatedatFactory $createdAtFactory
     * @param UpdatedatFactory $updatedAtFactory
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        FrameworkDateTime $dateTime,
        CreatedatFactory $createdAtFactory,
        UpdatedatFactory $updatedAtFactory,
        $connectionName = null
    ) {
        $this->_createdAtFactory = $createdAtFactory;
        $this->_updatedAtFactory = $updatedAtFactory;
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tax_order_aggregated_created', 'id');
    }

    /**
     * Aggregate Tax data
     *
     * @param mixed $from
     * @param mixed $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        /** @var Createdat $createdAt */
        $createdAt = $this->_createdAtFactory->create();
        /** @var Updatedat $updatedAt */
        $updatedAt = $this->_updatedAtFactory->create();

        $createdAt->aggregate($from, $to);
        $updatedAt->aggregate($from, $to);
        $this->_setFlagData(Flag::REPORT_TAX_FLAG_CODE);

        return $this;
    }
}

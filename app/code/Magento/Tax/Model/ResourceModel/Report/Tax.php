<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax report resource model
 */
namespace Magento\Tax\Model\ResourceModel\Report;

/**
 * Class \Magento\Tax\Model\ResourceModel\Report\Tax
 *
 */
class Tax extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
    /**
     * @var \Magento\Tax\Model\ResourceModel\Report\Tax\CreatedatFactory
     */
    protected $_createdAtFactory;

    /**
     * @var \Magento\Tax\Model\ResourceModel\Report\Tax\UpdatedatFactory
     */
    protected $_updatedAtFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Tax\Model\ResourceModel\Report\Tax\CreatedatFactory $createdAtFactory
     * @param \Magento\Tax\Model\ResourceModel\Report\Tax\UpdatedatFactory $updatedAtFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Tax\Model\ResourceModel\Report\Tax\CreatedatFactory $createdAtFactory,
        \Magento\Tax\Model\ResourceModel\Report\Tax\UpdatedatFactory $updatedAtFactory,
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
        /** @var $createdAt \Magento\Tax\Model\ResourceModel\Report\Tax\Createdat */
        $createdAt = $this->_createdAtFactory->create();
        /** @var $updatedAt \Magento\Tax\Model\ResourceModel\Report\Tax\Updatedat */
        $updatedAt = $this->_updatedAtFactory->create();

        $createdAt->aggregate($from, $to);
        $updatedAt->aggregate($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_TAX_FLAG_CODE);

        return $this;
    }
}

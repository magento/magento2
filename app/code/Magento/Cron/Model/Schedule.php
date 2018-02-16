<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cron\Model;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartFactory;
use Magento\Cron\Model\ResourceModel\Schedule\ExpressionFactory;
use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Crontab schedule model
 *
 * @method string getJobCode()
 * @method \Magento\Cron\Model\Schedule setJobCode(string $value)
 * @method string getStatus()
 * @method \Magento\Cron\Model\Schedule setStatus(string $value)
 * @method string getMessages()
 * @method \Magento\Cron\Model\Schedule setMessages(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Cron\Model\Schedule setCreatedAt(string $value)
 * @method string getScheduledAt()
 * @method \Magento\Cron\Model\Schedule setScheduledAt(string $value)
 * @method string getExecutedAt()
 * @method \Magento\Cron\Model\Schedule setExecutedAt(string $value)
 * @method string getFinishedAt()
 * @method \Magento\Cron\Model\Schedule setFinishedAt(string $value)
 * @method array getCronExprArr()
 * @method \Magento\Cron\Model\Schedule setCronExprArr(array $value)
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Schedule extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 'pending';

    const STATUS_RUNNING = 'running';

    const STATUS_SUCCESS = 'success';

    const STATUS_MISSED = 'missed';

    const STATUS_ERROR = 'error';

    /**
     * @var TimezoneInterface
     */
    private $timezoneConverter;

    /**
     * @var ExpressionFactory
     */
    private $expressionFactory;

    /**
     * @var ExpressionInterface
     */
    private $expression;

    /**
     * @var PartFactory
     */
    private $partFactory;

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     * @param TimezoneInterface                                       $timezoneConverter
     * @param ExpressionFactory                                       $expressionFactory
     * @param PartFactory                                             $partFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        TimezoneInterface $timezoneConverter = null,
        ExpressionFactory $expressionFactory = null,
        PartFactory $partFactory = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->timezoneConverter = $timezoneConverter ?: ObjectManager::getInstance()->get(TimezoneInterface::class);
        $this->expressionFactory = $expressionFactory ?: ObjectManager::getInstance()->get(ExpressionFactory::class);
        $this->expression = $this->expressionFactory->create();
        $this->partFactory = $partFactory ?: ObjectManager::getInstance()->get(PartFactory::class);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magento\Cron\Model\ResourceModel\Schedule::class);
    }

    /**
     * @param string $cronExpr
     *
     * @return $this
     * @throws \Magento\Framework\Exception\CronException
     */
    public function setCronExpr($cronExpr)
    {
        $this->expression->setCronExpr($cronExpr);
        return $this;
    }

    /**
     * Checks the observer's cron expression against time
     *
     * Supports $this->setCronExpr('* 0-5,10-59/5 2-10,15-25 january-june/2 mon-fri')
     *
     * @return bool
     */
    public function trySchedule()
    {
        $time = $this->getScheduledAt();

        if (!$time) {
            return false;
        }

        if (!is_numeric($time)) {
            //convert time from UTC to admin store timezone
            //we assume that all schedules in configuration (crontab.xml and DB tables) are in admin store timezone
            $date = $this->timezoneConverter->date($time);
            $time = $date->format('Y-m-d H:i');
            $time = strtotime($time);
        }

        return $this->expression->match($time);
    }

    /**
     * @param string $cronExprPart
     * @param int    $number
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CronException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @deprecated
     */
    public function matchCronExpression($cronExprPart, $number)
    {
        $part = $this->partFactory->create(PartFactory::GENERIC_PART, $cronExprPart);
        if (!$part->validate()) {
            throw new CronException(__('Invalid cron expression part: %1', $cronExprPart));
        }

        return $part->match($number);
    }

    /**
     * @param int|string $value
     *
     * @return bool|int|string
     *
     * @deprecated
     * @see \Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Parser\NumericParser::parse
     */
    public function getNumeric($value)
    {
        static $data = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(substr($value, 0, 3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }

        return false;
    }

    /**
     * Lock the cron job so no other scheduled instances run simultaneously.
     *
     * Sets a job to STATUS_RUNNING only if it is currently in STATUS_PENDING
     * and no other jobs of the same code are currently in STATUS_RUNNING.
     * Returns true if status was changed and false otherwise.
     *
     * @return boolean
     */
    public function tryLockJob()
    {
        if ($this->_getResource()->trySetJobUniqueStatusAtomic(
            $this->getId(),
            self::STATUS_RUNNING,
            self::STATUS_PENDING
        )) {
            $this->setStatus(self::STATUS_RUNNING);
            return true;
        }
        return false;
    }
}
